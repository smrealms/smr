<?php
require_once('AbstractSmrPlayer.class.inc');

class DummyPlayer extends AbstractSmrPlayer {
	public function __construct($gameID=0,$playerName='Dummy',$raceID=1,$experience=1000,$alignment=100,$allianceID=0,$shipTypeID=60) {
		parent::__construct();
		$this->accountID				= 0;
		$this->gameID					= (int) $gameID;
		$this->playerName				= (string) $playerName;
		$this->playerID					= 0;
		$this->sectorID					= 0;
		$this->lastSectorID				= 0;
		$this->turns					= 1000;
		$this->newbieTurns				= 0;
		$this->lastNewsUpdate			= 0;
		$this->dead						= false;
		$this->landedOnPlanet			= false;
		$this->lastActive				= 0;
		$this->lastCPLAction			= 0;
		$this->raceID					= (int) $raceID;
		$this->credits					= 0;
		$this->experience				= (int) $experience;
		$this->alignment				= (int) $alignment;
		$this->militaryPayment			= 0;
		$this->allianceID				= (int) $allianceID;
		$this->shipID					= (int) $shipTypeID;
		$this->kills					= 0;
		$this->deaths					= 0;
		$this->lastPort					= 0;
		$this->bank						= 0;
		$this->zoom						= 0;
		
		$this->gadgets = array();
		$this->pureRelations = array();
		$this->bounties = array();
	}
	
	protected function getGadgetsData() {
	}
	
	protected function getPureRelationsData() {
	}
	
	protected function getHOFData() {
	}
	
	protected function getBountiesData() {
	}
	
	public function killPlayer($sectorID) {
//		$sector =& SmrSector::getSector($this->getGameID(),$sectorID);
		//msg taken care of in trader_att_proc.php
		// forget plotted course
//		$this->deletePlottedCourse();

//		$sector->diedHere($this);
		
		// if we are in an alliance we increase their deaths
//		if ($this->hasAlliance())
//			$this->db->query('UPDATE alliance SET alliance_deaths = alliance_deaths + 1 ' .
//						 'WHERE game_id = '.$this->getGameID().' AND alliance_id = '.$this->getAllianceID().' LIMIT 1');

		// record death stat
//		$this->increaseHOF(1,'dying','deaths');
		//record cost of ship lost
//		$this->increaseHOF($this->getShip()->getCost(),'dying','ship_cost_lost');
		// reset turns since last death
//		$this->setHOF(0,'turns_used');

		// 1/4 of ship value -> insurance
		$newCredits = round($this->getShip()->getCost() / 4);
		$old_speed = $this->getShip()->getSpeed();
			
		if ($newCredits < 100000)
			$newCredits = 100000;
		$this->setCredits($newCredits);

		// speed for pod
		$new_speed = 7;

		// adapt turns
//		$this->setTurns(round($this->turns / $old_speed * $new_speed),100);

		$this->setSectorID(1);
		$this->increaseDeaths(1);
		$this->setLandedOnPlanet(false);
		$this->setDead(true);
//		$this->setNewbieWarning(true);
		$this->getShip()->getPod();
		
	}
	
	public function setAllianceID($ID) {
		if($this->allianceID == $ID)
			return;
		$this->allianceID=$ID;
	}
	
	public function &killPlayerByPlayer(AbstractSmrPlayer $killer) {
		
		$return = array();
		$msg = $this->getDisplayName();
	
//		if(isset($ship_names[$killed_id])) {
//			$msg .= ' flying ';
//			if(!stristr($ship_names[$killed_id],'<img')){
//				if(stristr($ship_names[$killed_id],'<mar')) $msg .= '<span class="yellow">' . strip_tags($ship_names[$killed_id]) . '</span>';
//				else {
//					$msg .= $ship_names[$killed_id];
//				}
//			} else $msg .= strip_tags($ship_names[$killed_id], '<img>');
//		}
		$msg .= ' was destroyed by ' . $killer->getDisplayName();
//		if(isset($ship_names[$killer_id])) {
//			$msg .= ' flying ';
//			if(!stristr($ship_names[$killer_id],'<img')){
//				if(stristr($ship_names[$killer_id],'<mar')) $msg .= '<span class="yellow">' . strip_tags($ship_names[$killer_id]) . '</span>';
//				else {
//					$msg .= $ship_names[$killer_id];
//				}
//			} else $msg .= strip_tags($ship_names[$killer_id], '<img>');
//		}
		$msg .= ' in Sector&nbsp;#' . $this->getSectorID();
//		$this->db->query('INSERT INTO news (game_id,time,news_message,type) VALUES (' . $this->getGameID() . ',' . TIME . ',' . $this->db->escapeString($msg,true) . ',\'regular\')');
		
//		$killer->sendMessage($this->getAccountID(),MSG_PLAYER,'You were <span class="red">DESTROYED</span> by ' . $killer->getPlayerName() . ' in sector <span class="blue">#' . $this->getSectorID() . '</span>',false);
//		$this->sendMessage($killer->getAccountID(),MSG_PLAYER,'You <span class="red">DESTROYED</span> ' . $this->getPlayerName() . ' in sector <span class="blue">#' . $this->getSectorID() . '</span>',false);
		
		// The person who got the kill gains experience
		$gainedExp = ceil(0.75*((((($this->getLevelID() - $killer->getLevelID()) / max($this->getLevelID(), $killer->getLevelID())) + 1) * 0.04 * $this->getExperience()) + (0.025 * $this->getExperience())));
		
		$return['KillerExp'] = $gainedExp;
		$killer->increaseExperience($gainedExp);
//		$killer->increaseHOF($gainedExp,'kill_exp_gain');
//		$killer->increaseHOF($this->getExperience(),'traders_killed_exp');
		// Dead player loses experience
		$expLossPercentage = 0.20 + (($this->getLevelID() - $killer->getLevelID()) * 0.005);
		$return['DeadExp'] = 0;
		if($expLossPercentage > 0) {
			$lostExp = floor($this->getExperience() * $expLossPercentage);
			$return['DeadExp'] = $lostExp;
			$this->decreaseExperience($lostExp);
		}
		
		$creditsGained = $this->getCredits();
		$return['KillerCredits'] = $creditsGained;
		$killer->increaseCredits($creditsGained);
//		$killer->increaseHOF($creditsGained,'killing','money_gain');
//		$killer->increaseHOF($this->getShip()->getCost(),'killing','ships_killed_cost');

		// The killer may change alignment
		$relations = Globals::getRaceRelations($this->getGameID(), $this->getRaceID());
		$relation = $relations[$killer->getRaceID()];

		$alignChangePerRelation = 0.1;
		if($relation >= RELATIONS_PEACE || $relation <= RELATIONS_WAR)
			$alignChangePerRelation = 0.04;

		$alignChange = $relation * $alignChangePerRelation;
		$return['KillerAlign'] = $alignChange;
		if($alignChange>0)
			$killer->increaseAlignment($alignChange);
		else
			$killer->decreaseAlignment(-$alignChange);
		// War setting gives them military pay
		if($relation <= RELATIONS_WAR) {
			$killer->increaseMilitaryPayment(-floor($relation * 100 * (pow($gainedExp/2,0.25))));
		}
		
		//check for federal bounty being offered for current port raiders
//		$allowed = TIME - 60 * 60 * 3;
//		$this->db->query('DELETE FROM player_attacks_port WHERE time < '.$allowed);
//		$query = 'SELECT count(*) as numAttacks
//					FROM player_attacks_port, player, port
//					WHERE player_attacks_port.game_id = port.game_id
//					AND port.game_id = player.game_id
//					AND armour > 0
//					AND player_attacks_port.sector_id = port.sector_id
//					AND player.account_id = player_attacks_port.account_id
//					AND player.account_id = ' . $this->getAccountID() . '
//					AND player.game_id = ' . $this->getGameID();
//		$this->db->query($query);
//		if ($this->db->nextRecord()) {
//			if ($this->db->getField('numAttacks')) {
//				$numAttacks = $this->db->getField('numAttacks');
//				$multiplier = round(.4 * $this->getLevelID());
//				$this->increaseCurrentBountyAmount('HQ',$numAttacks * 1000000 * $multiplier);
//			}
//		}
	
		// Killer get marked as claimer of podded player's bounties even if they don't exist
		$this->setBountiesClaimable($killer);

		// If the alignment difference is greater than 200 then a bounty may be set
		$alignment_diff = abs($this->getAlignment() - $killer->getAlignment());
		if($alignment_diff >= 200) {
			// If the podded players alignment makes them deputy or member then set bounty
			if($this->getAlignment() >= 100) {
				$bounty_type = 'HQ';
			}
			else if ($this->getAlignment() <= 100) {
				$bounty_type = 'UG';
			}

			if(isset($bounty_type)) {
				$killer->increaseCurrentBountyAmount($bounty_type,floor(pow($alignment_diff, 2.56)));
			}
		}
		
		$killer->increaseKills(1);
//		$killer->increaseHOF(1,'killing','kills');

//		if($this->hasAlliance()) {
//			$this->db->query('UPDATE alliance SET alliance_deaths=alliance_deaths+1 WHERE alliance_id=' . $this->getAllianceID() . ' AND game_id=' . $this->getGameID() . ' LIMIT 1');
//		}
//		if($killer->hasAlliance()) {
//			$this->db->query('UPDATE alliance SET alliance_kills=alliance_kills+1 WHERE alliance_id=' . $killer->getAllianceID() . ' AND game_id=' . $killer->getGameID() . ' LIMIT 1');
//		}
//
//		if($this->hasAlliance() && $killer->hasAlliance()) {
//			$this->db->query('SELECT kills FROM alliance_vs_alliance WHERE alliance_id_1=' . $killer->getAllianceID() . ' AND alliance_id_2=' . $this->getAllianceID() . ' AND game_id=' . $this->getGameID() . ' LIMIT 1');
//			if($this->db->nextRecord()) {
//				$this->db->query('UPDATE alliance_vs_alliance SET kills=kills+1 WHERE alliance_id_1=' . $this->getAllianceID() . ' AND alliance_id_2=' . $this->getAllianceID() . ' AND game_id=' . $this->getGameID() . ' LIMIT 1');
//			}
//			else {
//				$this->db->query('INSERT INTO alliance_vs_alliance VALUES (' . $this->getGameID() . ',' . $killer->getAllianceID() . ',' . $this->getAllianceID() . ',1)');
//			}
//		}
		
		
		$this->killPlayer($this->getSectorID());
		return $return;
	}
	
	public function &killPlayerByForces(SmrForce $forces) {
	}
	
	public function &killPlayerByPort(SmrPort $port) {
	}
	
	public function &killPlayerByPlanet(SmrPlanet $planet) {
	}
	
	public function &getShip() {
		require_once(get_file_loc('DummyShip.class.inc'));
		return DummyShip::getCachedDummyShip($this);
	}
	
	public function cacheDummyPlayer() {
		$this->getShip()->cacheDummyShip();
		$cache = serialize($this);
		$db = new SmrMySqlDatabase();
		$db->query('REPLACE INTO cached_dummys ' .
					'(type, id, info) ' .
					'VALUES (\'DummyPlayer\', '.$db->escapeString($this->getPlayerName()).', '.$db->escapeString($cache).')');	
		 unserialize($cache);
	}
	
	public static function &getCachedDummyPlayer($name) {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT info FROM cached_dummys
					WHERE type = \'DummyPlayer\'
						AND id = ' . $db->escapeString($name) . ' LIMIT 1');
		if($db->nextRecord()) {
			$return = unserialize($db->getField('info'));
			return $return;
		}
		else {
			$return = new DummyPlayer();
			return $return;
		}
	}
	
	public static function getDummyPlayerNames() {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT id FROM cached_dummys
					WHERE type = \'DummyPlayer\'');
		$dummyNames = array();
		while($db->nextRecord()) {
			$dummyNames[] = $db->getField('id');
		}
		return $dummyNames;
	}
}
