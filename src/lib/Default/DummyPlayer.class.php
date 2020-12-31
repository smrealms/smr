<?php declare(strict_types=1);

class DummyPlayer extends AbstractSmrPlayer {
	public function __construct($gameID=0,$playerName='Dummy',$raceID=1,$experience=1000,$alignment=100,$allianceID=0,$shipTypeID=60) {
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

		$this->personalRelations = array();
		$this->bounties = array();
	}

	protected function getPersonalRelationsData() {
	}

	protected function getHOFData() {
	}

	protected function getBountiesData() {
	}

	public function killPlayer($sectorID) {
		$this->setSectorID(1);
		$this->setDead(true);
		$this->getShip()->getPod();
	}

	public function setAllianceID($ID) {
		if($this->allianceID == $ID)
			return;
		$this->allianceID=$ID;
	}

	public function &killPlayerByPlayer(AbstractSmrPlayer $killer) {
		$this->killPlayer($this->getSectorID());
	}

	public function &killPlayerByForces(SmrForce $forces) {
	}

	public function &killPlayerByPort(SmrPort $port) {
	}

	public function &killPlayerByPlanet(SmrPlanet $planet) {
	}

	public function getShip() {
		return DummyShip::getCachedDummyShip($this);
	}

	public function cacheDummyPlayer() {
		$this->getShip()->cacheDummyShip();
		$cache = serialize($this);
		$db = MySqlDatabase::getInstance();
		$db->query('REPLACE INTO cached_dummys ' .
					'(type, id, info) ' .
					'VALUES (\'DummyPlayer\', '.$db->escapeString($this->getPlayerName()).', '.$db->escapeString($cache).')');
		 unserialize($cache);
	}

	public static function &getCachedDummyPlayer($name) {
		$db = MySqlDatabase::getInstance();
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
		$db = MySqlDatabase::getInstance();
		$db->query('SELECT id FROM cached_dummys
					WHERE type = \'DummyPlayer\'');
		$dummyNames = array();
		while($db->nextRecord()) {
			$dummyNames[] = $db->getField('id');
		}
		return $dummyNames;
	}
}
