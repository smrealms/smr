<?php
/**
 * Handles all Research related tasks and duties.
 * Researches are bound to alliances and are grouped in
 * Ship Research
 * Weapon Research
 *
 * User: tmaus
 * Date: 14.09.13
 * Time: 22:14
 */
require_once(get_file_loc('SmrAlliance.class.inc'));
require_once(get_file_loc('SmrPlayer.class.inc'));

class Research {
    protected $game;
    protected $db;
    protected $gameResearch = null;

    function __construct() {
        $this->db = new SmrMySqlDatabase();
    }

    /**
     * Fetches all available ship researches for the given game
     * @param $gameResearchId
     * @return array
     *      ship_type_id => "foreign key" on ship_type_id
     *      alliance_id => alliance_id in case research has been passed by that alliance
     */
    public function getGameResearchableShips(&$gameResearchId){
        $this->db->query(    "SELECT gc.ship_type_id, ga.alliance_id "
                            ." FROM smr.game_research_ship_certificate  gc"
                            ." JOIN smr.game_research_certificate gr "
	                        ." ON gc.game_research_certificate_id=gr.id "
                            ." LEFT JOIN smr.game_alliance_ship_certificate ga "
	                        ." ON ga.game_research_ship_certificate_id= gc.id "
                            ." WHERE gr.game_research_id=".$this->db->escapeNumber($gameResearchId));

        $result = array();
        while ($this->db->nextRecord()){
            $result[] = $this->db->getRow();
        }
        return $result;
    }

    public function deleteResearchShipCertificate(&$researchShipCertificateId){
        $this->db->query("DELETE FROM game_research_ship_certificate WHERE id=".$this->db->escapeNumber($researchShipCertificateId));
    }

    public function getResearchShipCertificates(&$gameResearchId){
        $query = "SELECT grsc.id, st.ship_name, cert.label, cert2.label as parent "
                        ." FROM game_research_ship_certificate grsc, game_research_certificate cert, ship_type st "
                        ." LEFT JOIN game_research_certificate cert2 ON parent_id=cert2.id "
                        ." WHERE grsc.game_research_certificate_id=cert.id "
                        ." AND grsc.ship_type_id=st.ship_type_id "
                        ." AND cert.game_research_id=".$this->db->escapeNumber($gameResearchId);
        $this->db->query($query);

        $result = array();
        while ($this->db->nextRecord()){
            $result[] = $this->db->getRow();
        }
        return $result;
    }

    public function assignResearchCertificateToShipType($researchCertificateId, $shipTypeId, $parentId){
        $this->db->query("SELECT * FROM game_research_ship_certificate WHERE ".
                    " game_research_certificate_id=".$this->db->escapeNumber($researchCertificateId).
                    " AND ship_type_id=".$this->db->escapeNumber($shipTypeId));

        if($this->db->nextRecord()){
            return;
        }

        $query = "INSERT INTO game_research_ship_certificate (game_research_certificate_id, ship_type_id";
        if(isset($parentId)){
            $query .= ", parent_id";
        }
        $query .= ") VALUES (".$this->db->escapeNumber($researchCertificateId).
                    ", ".$this->db->escapeNumber($shipTypeId);

        if(isset($parentId)){
            $query .= ", ".$this->db->escapeNumber($parentId);
        }
        $query .=")";

        $this->db->query($query);
    }

    /**
     * Deletes the row identified by the researchCertificateId
     * Updates all entries that have their parent_id=researchCertificateId by setting it to NULL
     * @param $researchCertificateId
     */
    public function deleteResearchCertificate(&$researchCertificateId){
        $this->db->query("UPDATE game_research_certificate SET parent_id=NULL WHERE parent_id=".$this->db->escapeNumber($researchCertificateId));

        $this->db->query("DELETE FROM game_research_certificate WHERE id=".$this->db->escapeNumber($researchCertificateId));
    }

    /**
     * Fetches all research certificates + the label (parent_label) of its predecessor
     * @param $gameResearchId
     */
    public function getResearchCertificates(&$gameResearchId){
        $gameResearchCertificates = null;
        $this->db->query('SELECT c.*, race.race_name FROM game_research_certificate c LEFT JOIN race ON c.race_id=race.race_id WHERE game_research_id='.$this->db->escapeNumber($gameResearchId)." ORDER BY parent_id");
        while ($this->db->nextRecord()){
            $gameResearchCertificates[] = $this->db->getRow();
        }
        if(!isset($gameResearchCertificates)){
            return;
        }
        foreach($gameResearchCertificates AS &$cert){
            if($cert['parent_id'] != null){
                foreach($gameResearchCertificates AS $parent){
                    if($cert['parent_id']==$parent['id']){
                        $cert['parent_label'] = $parent['label'];
                        break;
                    }
                }
            }
        }
        return $gameResearchCertificates;
    }

    /**
     * Adds a new Research Certificate
     * @TODO check for valid entries for parentId && raceId
     * @param MySqlDatabase $db
     * @param $request
     */
    public function addResearchCertificate(&$gameResearchId, &$label, &$raceId, &$duration, &$iteration, &$parentId, &$combinedResearch){

        $query = "INSERT INTO game_research_certificate (game_research_id,label, duration, iteration) "
            ." VALUES (".$this->db->escapeNumber($gameResearchId).",".$this->db->escapeString($label).","
            .$this->db->escapeNumber($duration).",".$this->db->escapeNumber($iteration).")";

        $this->db->query($query);
        $gameResearchCertificateId = $this->db->getInsertID();

        if($raceId){
            $this->db->query("UPDATE game_research_certificate SET race_id=".$this->db->escapeNumber($raceId)." WHERE id=".$gameResearchCertificateId);
        }
        if($parentId){
            $this->db->query("UPDATE game_research_certificate SET parent_id=".$this->db->escapeNumber($parentId). " WHERE id=".$gameResearchCertificateId);
        }
        if($combinedResearch){
            $this->db->query("UPDATE game_research_certificate SET combined_research=TRUE WHERE id=".$gameResearchCertificateId);
        }

    }

    /**
     * Fetches the GameResearch db entry or creates a new one if none existent for the provided gameId
     * @param $gameId
     * @return db::game_research mapping
     */
    public function getGameResearch(&$gameId){
        $this->db->query("SELECT * from game_research WHERE game_id=".$this->db->escapeNumber($gameId));

        if(!$this->db->nextRecord()){
            $this->db->query("INSERT INTO game_research (game_id) VALUES (".$this->db->escapeNumber($gameId).")");
            $this->db->query("SELECT * from game_research WHERE game_id=".$this->db->escapeNumber($gameId));
        }

        $this->gameResearch =  $this->db->fetch_row();
        return $this->gameResearch;
    }



    public function getResearchLevels(){

    }



    public function getShipResearchLevel(){

    }

    public function startShipResearchLevel(){

    }





}