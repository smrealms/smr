<?php declare(strict_types=1);

/**
 * Defines enhanced weapon sale events for weapon shops.
 */
class SmrEnhancedWeaponEvent {

	const GRACE_PERIOD = 3600; // 1 hour
	const DURATION = 21600; // 6 hours

	protected int $gameID;
	protected int $sectorID;
	protected int $locationTypeID;
	protected int $expires;
	protected SmrWeapon $weapon;

	/**
	 * Return all the valid events for the given location in a sector.
	 */
	public static function getShopEvents(int $gameID, int $sectorID, int $locationID) : array {
		$events = [];
		$db = new SmrMySqlDatabase();
		$db->query('SELECT * FROM location_sells_special WHERE sector_id = ' . $db->escapeNumber($sectorID) . ' AND location_type_id = ' . $db->escapeNumber($locationID) . ' AND game_id = ' . $db->escapeNumber($gameID) . ' AND expires > ' . $db->escapeNumber(TIME));
		while ($db->nextRecord()) {
			$events[] = self::getEventFromDatabase($db);
		}
		return $events;
	}

	/**
	 * Get the most recent event.
	 *
	 * This function also does the work of cleaning up expired events and
	 * creating new ones when necessary.
	 */
	public static function getLatestEvent(int $gameID) : SmrEnhancedWeaponEvent {
		// First, remove any expired events from the database
		$db = new SmrMySqlDatabase();
		$db->query('DELETE FROM location_sells_special WHERE expires < ' . $db->escapeNumber(TIME));

		// Next, check if an existing event can be advertised
		$db = new SmrMySqlDatabase();
		$db->query('SELECT * FROM location_sells_special WHERE game_id = ' . $db->escapeNumber($gameID) . ' ORDER BY expires DESC LIMIT 1');
		if ($db->nextRecord()) {
			$event = self::getEventFromDatabase($db);
			// Don't advertise if the event expires within one GRACE_PERIOD
			if (TIME < $event->getExpireTime() - self::GRACE_PERIOD) {
				return $event;
			}
		}

		// Otherwise, create a new event
		return self::createEvent($gameID);
	}

	/**
	 * Create a new event.
	 *
	 * Events are generated randomly across all weapon types available in the
	 * game, and then randomly across locations that offer that weapon type.
	 */
	private static function createEvent(int $gameID) : SmrEnhancedWeaponEvent {
		// First, randomly select a weapon type to enhance
		$weaponTypeID = array_rand(SmrWeaponType::getAllSoldWeaponTypes($gameID));

		$db = new SmrMySqlDatabase();
		$db->query('SELECT location_type_id, sector_id FROM location JOIN location_sells_weapons USING (location_type_id) WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND weapon_type_id = ' . $db->escapeNumber($weaponTypeID) . ' ORDER BY RAND() LIMIT 1');
		$db->requireRecord();
		$locationTypeID = $db->getInt('location_type_id');
		$sectorID = $db->getInt('sector_id');

		$expires = TIME + self::DURATION;

		// Determine which bonuses the weapon should have
		$random = rand(1, 100);
		if ($random <= 40) {
			$bonusAccuracy = false;
			$bonusDamage = true;
		} elseif ($random <= 80) {
			$bonusAccuracy = true;
			$bonusDamage = false;
		} else {
			$bonusAccuracy = true;
			$bonusDamage = true;
		}

		$db->query('INSERT INTO location_sells_special (game_id, sector_id, location_type_id, weapon_type_id, expires, bonus_accuracy, bonus_damage) VALUES (' . $db->escapeNumber($gameID) . ',' . $db->escapeNumber($sectorID) . ',' . $db->escapeNumber($locationTypeID) . ',' . $db->escapeNumber($weaponTypeID) . ',' . $db->escapeNumber($expires) . ',' . $db->escapeBoolean($bonusAccuracy) . ',' . $db->escapeBoolean($bonusDamage) . ')');

		return new SmrEnhancedWeaponEvent($gameID, $weaponTypeID, $locationTypeID, $sectorID, $expires, $bonusAccuracy, $bonusDamage);
	}

	/**
	 * Convenience function to instantiate an event from a query result.
	 */
	private static function getEventFromDatabase(SmrMySqlDatabase $db) : SmrEnhancedWeaponEvent {
		return new SmrEnhancedWeaponEvent($db->getInt('game_id'), $db->getInt('weapon_type_id'), $db->getInt('location_type_id'), $db->getInt('sector_id'), $db->getInt('expires'), $db->getBoolean('bonus_accuracy'), $db->getBoolean('bonus_damage'));
	}

	protected function __construct(int $gameID, int $weaponTypeID, int $locationTypeID, int $sectorID, int $expires, bool $bonusAccuracy, bool $bonusDamage) {
		$this->gameID = $gameID;
		$this->locationTypeID = $locationTypeID;
		$this->sectorID = $sectorID;
		$this->expires = $expires;

		$this->weapon = SmrWeapon::getWeapon($weaponTypeID);
		$this->weapon->setBonusDamage($bonusDamage);
		$this->weapon->setBonusAccuracy($bonusAccuracy);
	}

	public function getSectorID() : int {
		return $this->sectorID;
	}

	public function getExpireTime() : int {
		return $this->expires;
	}

	public function getWeapon() : SmrWeapon {
		return $this->weapon;
	}

}
