<?php declare(strict_types=1);

class DummyPlayer extends AbstractSmrPlayer {
	public function __construct(int $gameID = 0, string $playerName = 'Dummy', int $raceID = 1, int $experience = 1000, int $alignment = 100, int $allianceID = 0, int $shipTypeID = 60) {
		$this->gameID = (int)$gameID;
		$this->playerName = (string)$playerName;
		$this->dead = false;
		$this->raceID = (int)$raceID;
		$this->experience = (int)$experience;
		$this->alignment = (int)$alignment;
		$this->allianceID = (int)$allianceID;
		$this->shipID = (int)$shipTypeID;
	}

	public function killPlayer($sectorID) : void {
		$this->setDead(true);
		$this->getShip()->getPod();
	}

	public function setAllianceID(int $ID) : void {
		$this->allianceID = $ID;
	}

	public function killPlayerByPlayer(AbstractSmrPlayer $killer) : array {
		$this->killPlayer($this->getSectorID());
		return [];
	}

	public function getShip(bool $forceUpdate = false) : AbstractSmrShip {
		return DummyShip::getCachedDummyShip($this);
	}

	public function cacheDummyPlayer() : void {
		$this->getShip()->cacheDummyShip();
		$db = Smr\Database::getInstance();
		$db->query('REPLACE INTO cached_dummys ' .
					'(type, id, info) ' .
					'VALUES (\'DummyPlayer\', ' . $db->escapeString($this->getPlayerName()) . ', ' . $db->escapeObject($this) . ')');
	}

	public static function getCachedDummyPlayer(string $name) : self {
		$db = Smr\Database::getInstance();
		$db->query('SELECT info FROM cached_dummys
					WHERE type = \'DummyPlayer\'
						AND id = ' . $db->escapeString($name) . ' LIMIT 1');
		if ($db->nextRecord()) {
			return $db->getObject('info');
		} else {
			return new DummyPlayer();
		}
	}

	public static function getDummyPlayerNames() : array {
		$db = Smr\Database::getInstance();
		$db->query('SELECT id FROM cached_dummys
					WHERE type = \'DummyPlayer\'');
		$dummyNames = array();
		while ($db->nextRecord()) {
			$dummyNames[] = $db->getField('id');
		}
		return $dummyNames;
	}
}
