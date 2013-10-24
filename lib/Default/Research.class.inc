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

    // assure that multiple transactions within one request do not penetrate DB too much
    private $researchInProgressArray = array();

    public  $RESEARCH_PROCESS_STATES = array('progress'=>'IN_PROGRESS', 'msp'=>'MILESTONE_PASSED', 'psd'=>'PASSED');


    function __construct($gameId) {
        $this->db = new SmrMySqlDatabase();
        $this->getGameResearch($gameId);
    }

    public function startShipResearch($gameId, $player, $researchCertificateId){
        //$researchable = $this->getAllianceResearchableShipCertificates($player->getAllianceID());
        if($this->isPlanetInResearch($player->getSectorID())){
            return false;
        }

        $query = "INSERT INTO game_alliance_research_progress (game_research_ship_certificate_id, started_at, player_id, status, alliance_id, sector_id) "
                ." VALUES (".$this->db->escape($researchCertificateId)
                .",".time()
                .",".$player->getPlayerID()
                .",'".$this->RESEARCH_PROCESS_STATES['progress']."'"
                .",".$player->getAllianceID()
                .",".$player->getSectorID()
                .")";

        $this->db->query($query);
        return true;
    }

    /**
     * @param $allianceId
     * @return array
     */
    public function getAllianceResearchableShipCertificates($allianceId){

        // fetch the researches the alliance has already passed
        $researchedCerts = $this->getAllianceResearchedCertificates($this->gameResearch['id'], $allianceId);
        $researchedCertIds = 0;
        if($researchedCerts){
            $arr = array();
            foreach($researchedCerts AS &$rs){
                $arr[] = $rs['cert_id'];
            }
            $researchedCertIds = implode(",", $arr);
            unset($arr);
        }

        // fetch researchable certificates
        $this->db->query(  " SELECT grc.*, grsc.id AS research_ship_cert_id, r.race_name AS race, st.ship_name "
        ." FROM game_research_certificate grc "
        ." LEFT JOIN race r ON grc.race_id=r.race_id "
        ." LEFT JOIN game_research_ship_certificate grsc ON grsc.game_research_certificate_id=grc.id "
        ." LEFT JOIN ship_type st ON grsc.ship_type_id=st.ship_type_id "
        ." WHERE (grc.parent_id IS null OR grc.parent_id IN (".$researchedCertIds.")) "
        ." AND game_research_id=".$this->db->escapeNumber($this->gameResearch['id']));

        $result = array();
        while ($this->db->nextRecord()){
            $row = $this->db->getRow();
            if(!isset($result["l_".$row['id']])){
                $shipNames = array();
                $shipNames[] = $row['ship_name'];
                $row['ship_names'] = $shipNames;
                $result["l_".$row['id']]=$row;
            }else{
                $r = &$result["l_".$row['id']];
                $shipNames = &$r['ship_names'];
                $shipNames[] = $row['ship_name'];
            }
            unset($shipNames);
        }
        return $result;
    }

    public function isPlanetInResearch(&$sectorId){

        $query = "SELECT COUNT(*) AS rows "
                ." FROM 	smr.game_alliance_research_progress garp, "
		        ." smr.game_research_ship_certificate grsc, "
		        ." smr.game_research_certificate grc, "
		        ." smr.game_research gr "
                ." WHERE garp.game_research_ship_certificate_id=grsc.id "
                ." AND grsc.game_research_certificate_id=grc.id "
                ." AND grc.game_research_id=gr.id "
                ." AND status='".$this->RESEARCH_PROCESS_STATES['progress']."'"
                ." AND sector_id=".$this->db->escapeNumber($sectorId)
                ." AND game_research_id=".$this->db->escape($this->gameResearch['id']);

        $this->db->query($query);
        if(!$this->db->nextRecord()){
            return false;
        }
        $row = $this->db->getRow();

        return $row['rows'] > 0;
        /*
        foreach($allianceResearchInProgressArr AS $r){
            if($r['sector_id']==$sectorId){
                return true;
            }
        }
        return false;
        */
    }

    public function isPlayerResearching(&$allianceResearchInProgressArr, &$playerId){
        foreach($allianceResearchInProgressArr AS $r){
            if($r['player_id']==$playerId){
                return true;
            }
        }
        return false;
    }

    /**
     * Checks for any ended research and updates the status of it accordingly
     * @param $gameResearchId
     * @param $allianceId
     */
    public function processAllianceResearchInProgress($player){
        if($player->getAllianceID() != null){
            $result = $this->getAllianceResearchInProgress($this->gameResearch['id'], $player->getAllianceID());
            if(!empty($result)){
                foreach($result AS $r){
                    if($r['status']==$this->RESEARCH_PROCESS_STATES['progress']){
                        $expires =  strtotime(" +".$r['cert_duration']." hours", strtotime($r['started_at']));
                        $now = time();
                        if(time()  > $expires){
                            $this->processAllianceResearchMilestone($result, $r);
                        }
                    }
                }
            }
        }
    }

    private function processAllianceResearchMilestone($inProgressArr, $research){

        if($research['cert_iteration'] == 1){
            $this->db->query("UPDATE game_alliance_research_progress "
                            ." SET status='".$this->RESEARCH_PROCESS_STATES['psd']."' WHERE id=".$research['id']);

            // @TODO send alliance message
            return;
        }

        $sessionsArr = array();
        foreach($inProgressArr AS $r){
            if($r['cert_id']==$research['cert_id'] && $r['status']==$this->RESEARCH_PROCESS_STATES['msp']){
                $sessionsArr[] = $r;
            }
        }

        if(count($sessionsArr) >= $research['cert_iteration'] ){
            foreach($sessionsArr AS $r){
                $this->db->query("UPDATE game_alliance_research_progress "
                ." SET status='".$this->RESEARCH_PROCESS_STATES['psd']."' WHERE id=".$r['id']);
            }
            // @TODO send alliance message

        }else{
            $this->db->query("UPDATE game_alliance_research_progress "
            ." SET status='".$this->RESEARCH_PROCESS_STATES['msp']."' WHERE id=".$research['id']);

            // @TODO send alliance message
        }
    }

    /**
     * Returns the researches that are currently in progress
     * @param $allianceId
     * @return array (  'id': game_alliance_research_progress_id
     *                  'started_at': timestamp
     *                  'player_id': player_id
     *                  'status': status
     *                  'sector_id': sector in which research is conducted
     *                  'cert_id': certificate id
     *                  'cert_label': name of the certificate
     *                  'cert_duration: duration for cert'
     *                  'cert_iteration' : iterations to pass cert)
     */
    public function getAllianceResearchInProgress($allianceId){
        if(!empty($this->researchInProgressArray)){
            return $this->researchInProgressArray;
        }
        $this->db->query(   "SELECT garp.id, garp.started_at, garp.player_id, garp.status, garp.sector_id, grc.id AS cert_id, "
                            ." grc.label AS cert_label, grc.duration AS cert_duration, grc.iteration AS cert_iteration "
                            ." FROM game_alliance_research_progress garp, "
		                    ." game_research_ship_certificate grsc, "
		                    ." game_research_certificate grc, "
		                    ." game_research gr "
                            ." WHERE garp.game_research_ship_certificate_id=grsc.id "
                            ." AND grsc.game_research_certificate_id=grc.id "
                            ." AND grc.game_research_id=gr.id "
                            ." AND game_research_id=".$this->db->escapeNumber($this->gameResearch['id'])
                            ." AND alliance_id=".$this->db->escapeNumber($allianceId)
                            ." AND status in ('".$this->RESEARCH_PROCESS_STATES['progress']."','".$this->RESEARCH_PROCESS_STATES['msp']."')");

        $result = array();
        while ($this->db->nextRecord()){
            $result[] = $this->db->getRow();
        }
        $this->researchInProgressArray = $result;
        return $result;
    }

    public function getAllianceInResearchCertificates( $allianceId){
        $this->db->query(   " SELECT garp.id, cert.label, garp.status, garp.player_id, p.player_name, r.race_id, r.race_name "
                            ." FROM game_alliance_research_progress garp, "
                            ." game_research_ship_certificate grsc, "
                            ." game_research_certificate cert, "
	                        ." game_research gr, "
	                        ." player p, "
	                        ." race r "
                            ." WHERE garp.game_research_ship_certificate_id=grsc.id "
                            ." AND grsc.game_research_certificate_id=cert.id "
                            ." AND cert.game_research_id=gr.id "
                            ." AND cert.game_research_id=".$this->db->escapeNumber($this->gameResearch['id'])
                            ." AND garp.alliance_id=".$this->db->escapeNumber($allianceId)
                            ." AND garp.player_id=p.player_id "
                            ." AND gr.game_id=p.game_id "
                            ." AND p.race_id=r.race_id"
                            ." AND status IN ('".$this->RESEARCH_PROCESS_STATES['progress']."','".$this->RESEARCH_PROCESS_STATES['msp'].")");

        $result = array();
        while ($this->db->nextRecord()){
            $result[] = $this->db->getRow();
        }
        return $result;
    }

    /**
     * @param $allianceId
     * @return array
     */
    public function getAllianceResearchedCertificates($allianceId){
        $this->db->query(    " SELECT cert.id AS cert_id, cert.label, cert.parent_id, st.ship_type_id,st.ship_name "
                            ." FROM game_research_certificate cert, "
	                        ." game_research_ship_certificate cf, "
	                        ." game_alliance_ship_certificate gacf, "
	                        ." ship_type st "
                            ." WHERE cert.id=cf.game_research_certificate_id "
                            ." AND cf.ship_type_id=st.ship_type_id "
                            ." AND cf.id=gacf.game_research_ship_certificate_id "
                            ." AND cert.game_research_id=".$this->db->escapeNumber($this->gameResearch['id'])
                            ." AND gacf.alliance_id=".$this->db->escapeNumber($allianceId));

        $result = array();
        while ($this->db->nextRecord()){
            $result[] = $this->db->getRow();
        }
        return $result;
    }


    /**
     * @param $allianceId
     * @return array
     */
    public function getAllianceResearchableCertificates($allianceId){

        // fetch the researches the alliance has already passed
        $researchedCerts = $this->getAllianceResearchedCertificates($this->gameResearch['id'], $allianceId);
        $researchedCertIds = 0;
        if($researchedCerts){
            $arr = array();
            foreach($researchedCerts AS &$rs){
                $arr[] = $rs['cert_id'];
            }
            $researchedCertIds = implode(",", $arr);
            unset($arr);
        }

        // fetch researchable certificates
        $this->db->query(  " SELECT grc.*, r.race_name AS race "
                          ." FROM game_research_certificate grc "
                          ." LEFT JOIN smr.race r ON grc.race_id=r.race_id "
                          ." WHERE (parent_id IS null OR parent_id IN (".$researchedCertIds.")) "
                          ." AND game_research_id=".$this->db->escapeNumber($this->gameResearch['id']));

        $result = array();
        while ($this->db->nextRecord()){
            $result[] = $this->db->getRow();
        }
        return $result;
    }

    /**
     * Fetches all available ship researches for the given game
     * @param $gameResearchId
     * @return array
     *      ship_type_id => "foreign key" on ship_type_id
     *      alliance_id => alliance_id in case research has been passed by that alliance
     */
    public function getGameResearchableShips(){
        $this->db->query(    "SELECT gc.ship_type_id, ga.alliance_id "
                            ." FROM game_research_ship_certificate  gc"
                            ." JOIN game_research_certificate gr "
	                        ." ON gc.game_research_certificate_id=gr.id "
                            ." LEFT JOIN game_alliance_ship_certificate ga "
	                        ." ON ga.game_research_ship_certificate_id= gc.id "
                            ." WHERE gr.game_research_id=".$this->db->escapeNumber($this->gameResearch['id']));

        $result = array();
        while ($this->db->nextRecord()){
            $result[] = $this->db->getRow();
        }
        return $result;
    }

    public function deleteResearchShipCertificate(&$researchShipCertificateId){
        $this->db->query("DELETE FROM game_research_ship_certificate WHERE id=".$this->db->escapeNumber($researchShipCertificateId));
    }

    public function getResearchShipCertificates(){
        $query = "SELECT grsc.id, st.ship_name, cert.label, cert2.label as parent "
                        ." FROM game_research_ship_certificate grsc, game_research_certificate cert, ship_type st "
                        ." LEFT JOIN game_research_certificate cert2 ON parent_id=cert2.id "
                        ." WHERE grsc.game_research_certificate_id=cert.id "
                        ." AND grsc.ship_type_id=st.ship_type_id "
                        ." AND cert.game_research_id=".$this->db->escapeNumber($this->gameResearch['id']);
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
    public function getResearchCertificates(){
        $gameResearchCertificates = null;
        $this->db->query('SELECT c.*, race.race_name FROM game_research_certificate c LEFT JOIN race ON c.race_id=race.race_id WHERE game_research_id='.$this->db->escapeNumber($this->gameResearch['id'])." ORDER BY parent_id");
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
    public function addResearchCertificate( &$label, &$raceId, &$duration, &$iteration, &$parentId, &$combinedResearch, &$credits, &$computer){

        $query = "INSERT INTO game_research_certificate (game_research_id,label, duration, iteration, credits, computer) "
            ." VALUES (".$this->db->escapeNumber($this->gameResearch['id']).",".$this->db->escapeString($label).","
            .$this->db->escapeNumber($duration).",".$this->db->escapeNumber($iteration).","
            .$this->db->escapeNumber($credits).",".$this->db->escapeNumber($computer).")";

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
    private function getGameResearch(&$gameId){
        $this->db->query("SELECT * from game_research WHERE game_id=".$this->db->escapeNumber($gameId));

        if(!$this->db->nextRecord()){
            $this->db->query("INSERT INTO game_research (game_id) VALUES (".$this->db->escapeNumber($gameId).")");
            $this->db->query("SELECT * from game_research WHERE game_id=".$this->db->escapeNumber($gameId));
        }

        $this->gameResearch =  $this->db->fetch_row();
        return $this->gameResearch;
    }



    public function getGameResearchAss(){
        return $this->gameResearch;
    }

}