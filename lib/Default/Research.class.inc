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