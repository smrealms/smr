<?
require_once('SmrMySqlDatabase.class.inc');

require_once(get_file_loc('SmrPlayer.class.inc'));
		
class SmrSector
{
	protected static $CACHE_SECTORS = array();
	
	protected $db;
	

	protected $gameID;
	protected $sector_id;
	protected $accountID; //Should this REALLY be here?
	protected $link_up;
	protected $link_down;
	protected $link_left;
	protected $link_right;
	protected $warp;
	protected $battles;
	protected $galaxyID;
	protected $galaxyName;
	protected $visited;
	protected $portInfo;
	protected $links;
	
	protected $localMapHREF;
	protected $currentSectorHREF;
	protected $scanSectorHREF;

	public static function &getSector($gameID,$sectorID,$accountID,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_SECTORS[$gameID . ':' . $sectorID . ':' . $accountID]))
		{
			$s = new SmrSector($gameID,$sectorID,$accountID);
			return self::$CACHE_SECTORS[$s->getGameID() . ':' . $s->getSectorID() . ':' . $accountID] =& $s;
		}
		return self::$CACHE_SECTORS[$gameID. ':' .$sectorID . ':' . $accountID];
		
	}
	
	protected function __construct($gameID, $sectorID, $accountID)
	{
		$this->db = new SmrMySqlDatabase();
		
		$this->sectorID = $sectorID;
		$this->gameID = $gameID;
		$this->accountID = $accountID;

		$this->db->query('SELECT * FROM sector, galaxy WHERE sector.galaxy_id = galaxy.galaxy_id AND ' .
														'sector.game_id = '.$this->gameID.' AND ' .
														'sector_id = '.$this->sectorID.' LIMIT 1');
		$this->db->nextRecord();
		$this->galaxyID	= $this->db->getField('galaxy_id');
		$this->galaxyName	= $this->db->getField('galaxy_name');
		$this->battles		= $this->db->getField('battles');
		if (empty($this->battles)) $this->battles = 0;
		
		$this->link_up		= $this->db->getField('link_up');
		$this->link_down	= $this->db->getField('link_down');
		$this->link_left	= $this->db->getField('link_left');
		$this->link_right	= $this->db->getField('link_right');


		$this->db->query('SELECT * FROM warp WHERE game_id = '.$this->gameID.' AND ' .
											  '(sector_id_1 = '.$this->sectorID.' OR ' .
											   'sector_id_2 = '.$this->sectorID.')');
		if ($this->db->nextRecord())
			$this->warp = ($this->db->getField('sector_id_1') == $this->sectorID) ? $this->db->getField('sector_id_2') : $this->db->getField('sector_id_1');

		$this->links = array();
		if($this->link_up)
			$this->links['Up']=$this->link_up;
		if($this->link_down)
			$this->links['Down']=$this->link_down;
		if($this->link_left)
			$this->links['Left']=$this->link_left;
		if($this->link_right)
			$this->links['Right']=$this->link_right;
		if($this->warp)
			$this->links['Warp']=$this->warp;
			
		// let forces expire (simply let all expire) (refresh first)
//		$db2 = new SMR_DB();
//		$time = time();
//		$db->query('SELECT * FROM force_refresh WHERE refresh_at <= '.$time);
//		while ($db->next_record()) {
//			
//			$total = $db->f('num_forces');
//			$days = ceil($total / 10);
//			if ($days > 5) $days = 5;
//			$ex_time = $db->f('refresh_at') + ($days * 86400);
//			$db2->query('UPDATE sector_has_forces SET expire_time = '.$ex_time.' WHERE game_id = '.$this->game_id.' AND sector_id = ' . $db->f('sector_id') . ' AND owner_id = ' . $db->f('owner_id'));
//			$db2->query('DELETE FROM force_refresh WHERE game_id = '.$this->game_id .
//					' AND sector_id = ' . $db->f('sector_id') . ' AND owner_id = ' . $db->f('owner_id'));
//						
//		}
		$this->db->query('DELETE FROM sector_has_forces WHERE expire_time < ' . TIME);
	}

	function update() {

		$this->db->query('UPDATE sector SET battles = '.$this->battles.' ' .
								   'WHERE game_id = '.$this->gameID.' AND ' .
										 'sector_id = '.$this->sectorID);
	}

	function mark_visited()
	{
		if($this->hasPort())
			$this->getPort()->addCachePort($this->getAccountID());

		//now delete the entry from visited
		if(!$this->isVisited())
			$this->db->query('DELETE FROM player_visited_sector ' .
					 'WHERE game_id = '.$this->getGameID().' AND sector_id = '.$this->getSectorID().' AND ' .
					 'account_id = '.$this->getAccountID().' LIMIT 1');
		$this->visited=true;
	}

	function has_good($good_id, $transaction) {

		$this->db->query('SELECT * FROM port NATURAL JOIN port_has_goods ' .
					 'WHERE port.sector_id = '.$this->sectorID.' AND ' .
						   'port.game_id = '.$this->gameID.' AND ' .
						   'good_id = '.$good_id.' AND ' .
						   'transaction_type = '.$this->db->escapeString($transaction));
		return $this->db->getNumRows();

	}

	function has_weapon_shop()
	{
		$this->db->query('SELECT sector_id FROM location NATURAL JOIN location_sells_weapons ' .
					 'WHERE sector_id = '.$this->sectorID.' AND ' .
						   'game_id = '.$this->gameID.' LIMIT 1');
		return $this->db->getNumRows();
	}

	function has_hq() {

		$this->db->query('SELECT sector_id FROM location NATURAL JOIN location_is_hq ' .
					 'WHERE sector_id = '.$this->sectorID.' AND ' .
						   'game_id = '.$this->gameID.' LIMIT 1');
		return $this->db->getNumRows();

	}

	function has_ug() {
		// Pointless doing a natural join on a table with one entry
		$this->db->query('SELECT sector_id FROM location ' .
					 'WHERE location_type_id=102 AND sector_id = '.$this->sectorID.' AND ' .
						   'game_id = '.$this->gameID.' LIMIT 1');
		return $this->db->getNumRows();

	}

	function has_ship_shop() {

		$this->db->query('SELECT sector_id FROM location NATURAL JOIN location_sells_ships ' .
					 'WHERE sector_id = '.$this->sectorID.' AND ' .
						   'game_id = '.$this->gameID.' LIMIT 1');
		return $this->db->getNumRows();

	}

	function has_fed_beacon() {
		// Pointless doing a natural join on a table with one entry
		$this->db->query('SELECT sector_id FROM location ' .
					 'WHERE location_type_id=201 AND sector_id = '.$this->sectorID.' AND ' .
						   'game_id = '.$this->gameID.' LIMIT 1');
		return $this->db->getNumRows();

	}

	function has_bar() {

		$this->db->query('SELECT sector_id FROM location NATURAL JOIN location_is_bar ' .
					 'WHERE sector_id = '.$this->sectorID.' AND ' .
						   'game_id = '.$this->gameID.' LIMIT 1');
		return $this->db->getNumRows();

	}

	function has_hardware_shop() {

		$this->db->query('SELECT sector_id FROM location NATURAL JOIN location_sells_hardware ' .
					 'WHERE sector_id = '.$this->sectorID.' AND ' .
						   'game_id = '.$this->gameID.' LIMIT 1');
		return $this->db->getNumRows();

	}

	function has_bank() {

		$this->db->query('SELECT sector_id FROM location NATURAL JOIN location_is_bank ' .
					 'WHERE sector_id = '.$this->sectorID.' AND ' .
						   'game_id = '.$this->gameID.' LIMIT 1');
		return $this->db->getNumRows();

	}

	function entering_sector() {

		global $player, $account;
		global $SCOUTMSG;

		// get our rank
		$rank_id = $account->get_rank();

		// iterate over all scout drones in sector
		$this->db->query('SELECT * FROM sector_has_forces ' .
					 'WHERE sector_id = '.$this->sectorID.' AND ' .
						   'game_id = '.$this->gameID.' AND ' .
						   'owner_id != '.$this->accountID.' AND ' .
						   'scout_drones > 0');
		while ($this->db->nextRecord()) {

			$owner =& SmrPlayer::getPlayer($this->db->getField('owner_id'), $this->gameID);
			if ($owner->alliance_id == $player->getAllianceID() && $player->getAllianceID() != 0) continue;

			// we may skip player if this is a protected gal.
			if ($this->is_protected_gal()) {

				$curr_account =& SmrAccount::getAccount($this->db->getField('owner_id'));

				if (different_level($rank_id, $curr_account->get_rank(), $account->veteran, $curr_account->veteran))
				continue;
			}

			// send scout messages to user
			$message = 'Your forces have spotted ' . $player->getDisplayName() . ' entering sector #'.$this->sectorID;
			$player->sendMessage($owner->accountID, $SCOUTMSG, $this->db->escapeString($message, false));
			//insert into ticker
			$db = new SMR_DB();
			$time = time();
			$db->query('SELECT * FROM player_has_ticker WHERE account_id = '.$owner->getAccountID().' AND game_id = '.$owner->getGameID().' AND type = \'scout\'');
			if ($db->next_record()) {

				$db->query('SELECT * FROM player_has_ticker WHERE account_id = '.$player->getAccountID().' AND type = \'block\'');
				if (!$db->next_record()) $db->query('UPDATE player_has_ticker SET recent = ' . $db->escape_string($message, false) . ', time = '.$time.' WHERE account_id = '.$owner->getAccountID().' AND game_id = '.$owner->getGameID());

			}

		}

	}

	function leaving_sector() {

		global $player, $account;
		global $SCOUTMSG;

		// get our rank
		$rank_id = $account->get_rank();

		// iterate over all scout drones in sector
		$this->db->query('SELECT * FROM sector_has_forces ' .
					 'WHERE sector_id = '.$this->sectorID.' AND ' .
						   'game_id = '.$this->gameID.' AND ' .
						   'owner_id != '.$this->accountID.' AND ' .
						   'scout_drones > 0');
		while ($this->db->nextRecord()) {

			$owner =& SmrPlayer::getPlayer($this->db->getField('owner_id'), $this->gameID);
			if ($owner->alliance_id == $player->getAllianceID() && $player->getAllianceID() != 0) continue;

			// we may skip player if this is a protected gal.
			if ($this->is_protected_gal()) {

				$curr_account =& SmrAccount::getAccount($this->db->getField('owner_id'));
				if (different_level($rank_id, $curr_account->get_rank(), $account->veteran, $curr_account->veteran)) continue;

			}

			// send scout messages to user
			$message = 'Your forces have spotted ' . $player->getDisplayName() . ' leaving sector #'.$this->sectorID;
			$player->sendMessage($owner->getAccountID(), $SCOUTMSG, $this->db->escapeString($message, false));
			//insert into ticker
			$db = new SMR_DB();
			$time = time();
			$db->query('SELECT * FROM player_has_ticker WHERE account_id = '.$owner->getAccountID().' AND game_id = '.$owner->getGameID().' AND type = \'scout\'');
			if ($db->next_record()) {

				$db->query('SELECT * FROM player_has_ticker WHERE account_id = '.$player->getAccountID().' AND type = \'block\'');
				if (!$db->next_record()) $db->query('UPDATE player_has_ticker SET recent = ' . $db->escape_string($message, false) . ', time = '.$time.' WHERE account_id = '.$owner->getAccountID().' AND game_id = '.$owner->getGameID());

			}

		}

	}

	function died_here(SmrPlayer &$player)
	{
		global $SCOUTMSG;
		$account =& $player->getAccount();

		// get our rank
		$rank_id = $account->get_rank();

		// the sector saw a battle now
		$this->db->query('UPDATE sector SET battles = battles + 1 ' .
					 'WHERE game_id = '.$this->getGameID().' AND sector_id = '.$this->getSectorID().' LIMIT 1');
		
		// iterate over all scout drones in sector
		$this->db->query('SELECT * FROM sector_has_forces ' .
					 'WHERE sector_id = '.$this->sectorID.' AND ' .
						   'game_id = '.$this->gameID.' AND ' .
						   'owner_id != '.$this->accountID.' AND ' .
						   'scout_drones > 0');
		while ($this->db->nextRecord()) {

			$owner =& SmrPlayer::getPlayer($this->db->getField('owner_id'), $this->gameID);
			if ($owner->sameAlliance($player)) continue;

			// we may skip player if this is a protected gal.
			if ($this->is_protected_gal()) {

				$curr_account =& SmrAccount::getAccount($this->db->getField('owner_id'));

				// if one is vet and the other is newbie we skip it
				if (different_level($rank_id, $curr_account->get_rank(), $account->veteran, $curr_account->veteran))
					continue;

			}

			// send scout messages to user
			$message = 'Your forces have spotted that ' . $player->getDisplayName() . ' has been <font color=red>DESTROYED</font> in sector #'.$this->sectorID;
			$player->sendMessage($owner->getAccountID(), $SCOUTMSG, $this->db->escapeString($message));
			//insert into ticker
			$db = new SMR_DB();
			$time = time();
			$db->query('SELECT * FROM player_has_ticker WHERE account_id = '.$owner->getAccountID().' AND game_id = '.$owner->getGameID().' AND type = \'scout\'');
			if ($db->next_record()) {

				$db->query('SELECT * FROM player_has_ticker WHERE account_id = '.$player->getAccountID().' AND type = \'block\'');
				if (!$db->next_record()) $db->query('UPDATE player_has_ticker SET recent = ' . $db->escape_string($message, false) . ', time = '.$time.' WHERE account_id = '.$owner->getAccountID().' AND game_id = '.$owner->getGameID());

			}

		}

	}

	function is_protected_gal() {

		return $this->galaxyID < 9;

	}
	
	public function getGameID()
	{
		return $this->gameID;
	}
	
	public function getAccountID()
	{
		return $this->accountID;
	}
	
	public function getSectorID()
	{
		return $this->sectorID;
	}
	
	public function getGalaxyID()
	{
		return $this->galaxyID;
	}
	
	public function getGalaxyName()
	{
		return $this->galaxyName;
	}
	
	public function getLinks()
	{
		return $this->links;
	}
	
	public function getLink($name)
	{
		return isset($this->links[$name]) ? $this->links[$name] : 0;
	}
	
	public function getLinkUp()
	{
		return $this->getLink('Up');
	}
	
	public function getLinkDown()
	{
		return $this->getLink('Down');
	}
	
	public function getLinkLeft()
	{
		return $this->getLink('Left');
	}
	
	public function getLinkRight()
	{
		return $this->getLink('Right');
	}
	
	public function getLinkWarp()
	{
		return $this->getLink('Warp');
	}
	
	public function &getLinkWarpSector()
	{
		return SmrSector::getSector($this->getGameID(),$this->getLink('Warp'),$this->getAccountID());
	}
	
	public function hasWarp()
	{
		return $this->getLinkWarp()>0;
	}
	
	public function hasMine()
	{
		//TODO
		return false;
	}

	public function hasPort()
	{
		return $this->getPort()->exists();
	}

	public function &getPort()
	{
		require_once(get_file_loc('SmrPort.class.inc'));
		return SmrPort::getPort($this->gameID,$this->sectorID);
	}

	public function hasCachedPort()
	{
		return $this->getCachedPort()!==false;
	}

	public function &getCachedPort()
	{
		require_once(get_file_loc('SmrPort.class.inc'));
		return SmrPort::getCachedPort($this->getGameID(),$this->getSectorID(),$this->getAccountID());
	}
	
	public function hasAnyLocationsWithAction()
	{
		require_once(get_file_loc('SmrLocation.class.inc'));
		$locations =& SmrLocation::getSectorLocations($this->getGameID(),$this->getSectorID());
		$hasAction = false;
		foreach($locations as $location)
		{
			if($location->hasAction())
				$hasAction = true;
		}
		return $hasAction;
	}
	
	public function hasLocation()
	{
		return count($this->getLocations()) > 0;
	}
	
	public function &getLocations()
	{
		require_once(get_file_loc('SmrLocation.class.inc'));
		return SmrLocation::getSectorLocations($this->getGameID(),$this->getSectorID());
	}

	public function hasPlanet()
	{
		return $this->getPlanet()->exists();
	}
	
	public function &getPlanet()
	{
		require_once(get_file_loc('SmrPlanet.class.inc'));
		return SmrPlanet::getPlanet($this->getGameID(),$this->getSectorID());
	}

	public function hasForces()
	{
		return count($this->getForces()) > 0;
	}

	public function &getForces()
	{
		require_once(get_file_loc('SmrForce.class.inc'));
		return SmrForce::getSectorForces($this->getGameID(),$this->getSectorID());
	}

	public function hasTraders()
	{
		return count($this->getTraders()) > 0;
	}

	public function hasOtherTraders()
	{
		return count($this->getOtherTraders()) > 0;
	}

	public function &getTraders()
	{
		return SmrPlayer::getSectorPlayers($this->getGameID(),$this->getSectorID());
	}

	public function &getFightingTraders(SmrPlayer &$attackingPlayer, SmrPlayer &$defendingPlayer)
	{
		if($attackingPlayer->traderNAPAlliance($defendingPlayer))
			throw new Exception('These traders are NAPed.');
		$fightingPlayers = array('Attackers' => array(), 'Defenders' => array());
		$alliancePlayers =& SmrPlayer::getSectorPlayersByAlliances($this->getGameID(),$this->getSectorID(), array($attackingPlayer->getAllianceID(), $defendingPlayer->getAllianceID()));
		foreach($alliancePlayers as $accountID => $player)
		{
			if($player->canFight())
			{
				if($attackingPlayer->traderAttackTraderAlliance($player) && !$defendingPlayer->traderDefendTraderAlliance($player) && !$defendingPlayer->traderNAPAlliance($player))
					$fightingPlayers['Attackers'][$accountID] =& $alliancePlayers[$accountID];
				else if($defendingPlayer->traderDefendTraderAlliance($player) && !$attackingPlayer->traderAttackTraderAlliance($player) && !$attackingPlayer->traderNAPAlliance($player))
					$fightingPlayers['Defenders'][$accountID] =& $alliancePlayers[$accountID];
			}
		}
		return $fightingPlayers;
	}

	public function &getOtherTraders()
	{
		$players = SmrPlayer::getSectorPlayers($this->getGameID(),$this->getSectorID()); //Do not use & because we unset something and only want that in what we return
		unset($players[$this->accountID]);
		return $players;
	}
	
	public function getBattles()
	{
		return $this->battles;
	}
	
	public function setBattles($amount)
	{
		$this->battles = $amount;
		$db->query('UPDATE sector SET '.$this->battles.' WHERE sector_id=' . $this->getSectorID() . ' AND game_id=' . $this->getGameID() . ' LIMIT 1');
	}
	
	public function decreaseBattles($amount)
	{
		$this->setBattles($this->battles-$amount);
	}
	
	public function increaseBattles($amount)
	{
		$this->setBattles($this->battles+$amount);
	}
	
	function equals($otherSector)
	{
		return $otherSector->getGameID()==$this->getGameID()&&$otherSector->getSectorID()==$this->getSectorID();
	}
	
	function isLinked($otherSector)
	{
		return $otherSector->getGameID()==$this->getGameID()&&in_array($otherSector->getSectorID(),$this->getLinks());
	}
	
	public function isVisited()
	{
		if(!isset($this->visited))
		{
			$this->db->query('SELECT sector_id FROM player_visited_sector WHERE sector_id = ' . $this->getSectorID() . ' AND account_id=' . $this->getAccountID() . ' AND game_id=' . $this->getGameID() .' LIMIT 1');
			$this->visited = !$this->db->nextRecord();
		}
		return $this->visited;
	}
	
	protected function getHREF($targetPage)
	{
		$container = array();
		$container['url'] = 'sector_move_processing.php';
		$container['target_page'] = $targetPage;
		$container['target_sector'] = $this->getSectorID();
		return SmrSession::get_new_href($container);
	}
	
	public function getLocalMapHREF()
	{
		if(!isset($this->localMapHREF))
		{
			$this->localMapHREF = $this->getHREF('map_local.php');
		}
		return $this->localMapHREF;
	}
	
	public function getCurrentSectorHREF()
	{
		if(!isset($this->currentSectorHREF))
		{
			$this->currentSectorHREF = $this->getHREF('current_sector.php');
		}
		return $this->currentSectorHREF;
	}
	
	public function getScanSectorHREF()
	{
		if(!isset($this->scanSectorHREF))
		{
			$container = array();
			$container['url'] = 'skeleton.php';
			$container['body'] = 'sector_scan.php';
			$container['target_sector'] = $this->getSectorID();
			$this->scanSectorHREF = SmrSession::get_new_href($container);
		}
		return $this->scanSectorHREF;
	}
}

?>