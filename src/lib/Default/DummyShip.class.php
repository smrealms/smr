<?php declare(strict_types=1);

class DummyShip extends AbstractSmrShip {
	protected static array $CACHED_DUMMY_SHIPS;

	public function __construct(AbstractSmrPlayer $player) {
		parent::__construct($player);
		$this->setHardwareToMax();
	}

	public function cacheDummyShip() : void {
		$db = Smr\Database::getInstance();
		$db->write('REPLACE INTO cached_dummys (type, id, info)
					VALUES (\'DummyShip\', ' . $db->escapeString($this->getPlayer()->getPlayerName()) . ', ' . $db->escapeObject($this) . ')');
	}

	public static function getCachedDummyShip(AbstractSmrPlayer $player) : self {
		if (!isset(self::$CACHED_DUMMY_SHIPS[$player->getPlayerName()])) {
			$ship = new DummyShip($player);

			// Load weapons from the dummy database cache, if available
			$db = Smr\Database::getInstance();
			$dbResult = $db->read('SELECT info FROM cached_dummys WHERE type = \'DummyShip\'
						AND id = ' . $db->escapeString($player->getPlayerName()) . ' LIMIT 1');
			if ($dbResult->hasRecord()) {
				$cachedShip = $dbResult->record()->getObject('info');
				foreach ($cachedShip->getWeapons() as $weapon) {
					$ship->addWeapon($weapon);
				}
			}

			self::$CACHED_DUMMY_SHIPS[$player->getPlayerName()] = $ship;
		}
		return self::$CACHED_DUMMY_SHIPS[$player->getPlayerName()];
	}

	public static function getDummyShipNames() : array {
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT id FROM cached_dummys WHERE type = \'DummyShip\'');
		$dummyNames = array();
		foreach ($dbResult->records() as $dbRecord) {
			$dummyNames[] = $dbRecord->getField('id');
		}
		return $dummyNames;
	}

	public function __sleep() {
		return array('weapons');
	}

}
