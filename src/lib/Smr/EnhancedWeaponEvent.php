<?php declare(strict_types=1);

namespace Smr;

use Smr\Combat\Weapon\Weapon;

/**
 * Defines enhanced weapon sale events for weapon shops.
 */
class EnhancedWeaponEvent {

	protected const int GRACE_PERIOD = 3600; // 1 hour
	protected const int DURATION = 21600; // 6 hours

	protected readonly Weapon $weapon;

	/**
	 * Return all the valid events for the given location in a sector.
	 *
	 * @return array<self>
	 */
	public static function getShopEvents(int $gameID, int $sectorID, int $locationID): array {
		$events = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM location_sells_special WHERE sector_id = :sector_id AND location_type_id = :location_type_id AND game_id = :game_id AND expires > :now', [
			'sector_id' => $db->escapeNumber($sectorID),
			'location_type_id' => $db->escapeNumber($locationID),
			'game_id' => $db->escapeNumber($gameID),
			'now' => $db->escapeNumber(Epoch::time()),
		]);
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
	public static function getLatestEvent(int $gameID): self {
		// First, remove any expired events from the database
		$db = Database::getInstance();
		$db->write('DELETE FROM location_sells_special WHERE expires < :now', [
			'now' => $db->escapeNumber(Epoch::time()),
		]);

		// Next, check if an existing event can be advertised
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM location_sells_special WHERE game_id = :game_id ORDER BY expires DESC LIMIT 1', [
			'game_id' => $db->escapeNumber($gameID),
		]);
		if ($dbResult->hasRecord()) {
			$event = self::getEventFromDatabase($dbResult->record());
			// Don't advertise if the event expires within one GRACE_PERIOD
			if (Epoch::time() < $event->getExpireTime() - self::GRACE_PERIOD) {
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
	private static function createEvent(int $gameID): self {
		// First, randomly select a weapon type to enhance
		$weaponTypeID = array_rand(WeaponType::getAllSoldWeaponTypes($gameID));

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT location_type_id, sector_id FROM location JOIN location_sells_weapons USING (location_type_id) WHERE game_id = :game_id AND weapon_type_id = :weapon_type_id ORDER BY RAND() LIMIT 1', [
			'game_id' => $db->escapeNumber($gameID),
			'weapon_type_id' => $db->escapeNumber($weaponTypeID),
		]);
		$dbRecord = $dbResult->record();
		$locationTypeID = $dbRecord->getInt('location_type_id');
		$sectorID = $dbRecord->getInt('sector_id');

		$expires = Epoch::time() + self::DURATION;

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

		// We replace instead of insert in the very unlikely case that we have
		// selected the same configuration twice in a row.
		$db->replace('location_sells_special', [
			'game_id' => $gameID,
			'sector_id' => $sectorID,
			'location_type_id' => $locationTypeID,
			'weapon_type_id' => $weaponTypeID,
			'expires' => $expires,
			'bonus_accuracy' => $db->escapeBoolean($bonusAccuracy),
			'bonus_damage' => $db->escapeBoolean($bonusDamage),
		]);

		return new self($gameID, $weaponTypeID, $locationTypeID, $sectorID, $expires, $bonusAccuracy, $bonusDamage);
	}

	/**
	 * Convenience function to instantiate an event from a query result.
	 */
	private static function getEventFromDatabase(DatabaseRecord $dbRecord): self {
		return new self(
			$dbRecord->getInt('game_id'),
			$dbRecord->getInt('weapon_type_id'),
			$dbRecord->getInt('location_type_id'),
			$dbRecord->getInt('sector_id'),
			$dbRecord->getInt('expires'),
			$dbRecord->getBoolean('bonus_accuracy'),
			$dbRecord->getBoolean('bonus_damage'),
		);
	}

	protected function __construct(
		protected readonly int $gameID,
		protected readonly int $weaponTypeID,
		protected readonly int $locationTypeID,
		protected readonly int $sectorID,
		protected readonly int $expires,
		bool $bonusAccuracy,
		bool $bonusDamage,
	) {
		$this->weapon = Weapon::getWeapon($weaponTypeID);
		$this->weapon->setBonusDamage($bonusDamage);
		$this->weapon->setBonusAccuracy($bonusAccuracy);
	}

	public function getSectorID(): int {
		return $this->sectorID;
	}

	public function getExpireTime(): int {
		return $this->expires;
	}

	public function getWeapon(): Weapon {
		return $this->weapon;
	}

	/**
	 * Returns the amount of time left in the event as a percent of the
	 * total duration of the event.
	 */
	public function getDurationRemainingPercent(): float {
		return max(0, min(100, ($this->expires - Epoch::time()) / self::DURATION * 100));
	}

}
