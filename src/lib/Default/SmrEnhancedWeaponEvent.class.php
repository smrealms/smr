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
	public static function getShopEvents(int $gameID, int $sectorID, int $locationID): array {
		$events = [];
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT * FROM location_sells_special WHERE sector_id = ' . $db->escapeNumber($sectorID) . ' AND location_type_id = ' . $db->escapeNumber($locationID) . ' AND game_id = ' . $db->escapeNumber($gameID) . ' AND expires > ' . $db->escapeNumber(Smr\Epoch::time()));
		foreach ($dbResult->records() as $dbRecord) {
			$events[] = self::getEventFromDatabase($dbRecord);
		}
		return $events;
	}

	/**
	 * Get the most recent event.
	 *
	 * This function also does the work of cleaning up expired events and
	 * creating new ones when necessary.
	 */
	public static function getLatestEvent(int $gameID): SmrEnhancedWeaponEvent {
		// First, remove any expired events from the database
		$db = Smr\Database::getInstance();
		$db->write('DELETE FROM location_sells_special WHERE expires < ' . $db->escapeNumber(Smr\Epoch::time()));

		// Next, check if an existing event can be advertised
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT * FROM location_sells_special WHERE game_id = ' . $db->escapeNumber($gameID) . ' ORDER BY expires DESC LIMIT 1');
		if ($dbResult->hasRecord()) {
			$event = self::getEventFromDatabase($dbResult->record());
			// Don't advertise if the event expires within one GRACE_PERIOD
			if (Smr\Epoch::time() < $event->getExpireTime() - self::GRACE_PERIOD) {
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
	private static function createEvent(int $gameID): SmrEnhancedWeaponEvent {
		// First, randomly select a weapon type to enhance
		$weaponTypeID = array_rand(SmrWeaponType::getAllSoldWeaponTypes($gameID));

		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT location_type_id, sector_id FROM location JOIN location_sells_weapons USING (location_type_id) WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND weapon_type_id = ' . $db->escapeNumber($weaponTypeID) . ' ORDER BY RAND() LIMIT 1');
		$dbRecord = $dbResult->record();
		$locationTypeID = $dbRecord->getInt('location_type_id');
		$sectorID = $dbRecord->getInt('sector_id');

		$expires = Smr\Epoch::time() + self::DURATION;

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

		$db->insert('location_sells_special', [
			'game_id' => $db->escapeNumber($gameID),
			'sector_id' => $db->escapeNumber($sectorID),
			'location_type_id' => $db->escapeNumber($locationTypeID),
			'weapon_type_id' => $db->escapeNumber($weaponTypeID),
			'expires' => $db->escapeNumber($expires),
			'bonus_accuracy' => $db->escapeBoolean($bonusAccuracy),
			'bonus_damage' => $db->escapeBoolean($bonusDamage),
		]);

		return new SmrEnhancedWeaponEvent($gameID, $weaponTypeID, $locationTypeID, $sectorID, $expires, $bonusAccuracy, $bonusDamage);
	}

	/**
	 * Convenience function to instantiate an event from a query result.
	 */
	private static function getEventFromDatabase(Smr\DatabaseRecord $dbRecord): SmrEnhancedWeaponEvent {
		return new SmrEnhancedWeaponEvent(
			$dbRecord->getInt('game_id'),
			$dbRecord->getInt('weapon_type_id'),
			$dbRecord->getInt('location_type_id'),
			$dbRecord->getInt('sector_id'),
			$dbRecord->getInt('expires'),
			$dbRecord->getBoolean('bonus_accuracy'),
			$dbRecord->getBoolean('bonus_damage'),
		);
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

	public function getSectorID(): int {
		return $this->sectorID;
	}

	public function getExpireTime(): int {
		return $this->expires;
	}

	public function getWeapon(): SmrWeapon {
		return $this->weapon;
	}

	/**
	 * Returns the amount of time left in the event as a percent of the
	 * total duration of the event.
	 */
	public function getDurationRemainingPercent(): float {
		return max(0, min(100, ($this->expires - Smr\Epoch::time()) / self::DURATION * 100));
	}

}
