<?php declare(strict_types=1);

class DummyShip extends AbstractSmrShip {
	protected static $CACHED_DUMMY_SHIPS;

	public function __construct(AbstractSmrPlayer $player) {
		parent::__construct($player);
		$this->setHardwareToMax();
	}

	public function cacheDummyShip() {
		$db = Smr\Database::getInstance();
		$db->query('REPLACE INTO cached_dummys (type, id, info)
					VALUES (\'DummyShip\', ' . $db->escapeString($this->getPlayer()->getPlayerName()) . ', ' . $db->escapeObject($this) . ')');
	}

	public static function getCachedDummyShip(AbstractSmrPlayer $player) {
		if (!isset(self::$CACHED_DUMMY_SHIPS[$player->getPlayerName()])) {
			$ship = new DummyShip($player);

			// Load weapons from the dummy database cache, if available
			$db = Smr\Database::getInstance();
			$db->query('SELECT info FROM cached_dummys WHERE type = \'DummyShip\'
						AND id = ' . $db->escapeString($player->getPlayerName()) . ' LIMIT 1');
			if ($db->nextRecord()) {
				$cachedShip = $db->getObject('info');
				foreach ($cachedShip->getWeapons() as $weapon) {
					$ship->addWeapon($weapon);
				}
			}

			self::$CACHED_DUMMY_SHIPS[$player->getPlayerName()] = $ship;
		}
		return self::$CACHED_DUMMY_SHIPS[$player->getPlayerName()];
	}

	public static function getDummyShipNames() {
		$db = Smr\Database::getInstance();
		$db->query('SELECT id FROM cached_dummys WHERE type = \'DummyShip\'');
		$dummyNames = array();
		while ($db->nextRecord()) {
			$dummyNames[] = $db->getField('id');
		}
		return $dummyNames;
	}

	public function __sleep() {
		return array('weapons');
	}

}
