<?php declare(strict_types=1);

/**
 * Defines the base weapon types for ships/planets.
 */
class SmrWeaponType {
	use Traits\RaceID;

	protected static array $CACHE_WEAPON_TYPES = [];

	protected int $weaponTypeID;
	protected string $name;
	protected int $cost;
	protected int $shieldDamage;
	protected int $armourDamage;
	protected int $accuracy;
	protected int $powerLevel;
	protected int $buyerRestriction;

	public static function getWeaponType(int $weaponTypeID, Smr\DatabaseRecord $dbRecord = null): SmrWeaponType {
		if (!isset(self::$CACHE_WEAPON_TYPES[$weaponTypeID])) {
			if ($dbRecord === null) {
				$db = Smr\Database::getInstance();
				$dbResult = $db->read('SELECT * FROM weapon_type WHERE weapon_type_id = ' . $db->escapeNumber($weaponTypeID));
				$dbRecord = $dbResult->record();
			}
			$weapon = new SmrWeaponType($weaponTypeID, $dbRecord);
			self::$CACHE_WEAPON_TYPES[$weaponTypeID] = $weapon;
		}
		return self::$CACHE_WEAPON_TYPES[$weaponTypeID];
	}

	public static function getAllWeaponTypes(): array {
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT * FROM weapon_type');
		$weapons = [];
		foreach ($dbResult->records() as $dbRecord) {
			$weaponTypeID = $dbRecord->getInt('weapon_type_id');
			$weapons[$weaponTypeID] = self::getWeaponType($weaponTypeID, $dbRecord);
		}
		return $weapons;
	}

	/**
	 * Returns all weapon types that are purchasable in the given game.
	 */
	public static function getAllSoldWeaponTypes(int $gameID): array {
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT DISTINCT weapon_type.* FROM weapon_type JOIN location_sells_weapons USING (weapon_type_id) JOIN location USING (location_type_id) WHERE game_id = ' . $db->escapeNumber($gameID));
		$weapons = [];
		foreach ($dbResult->records() as $dbRecord) {
			$weaponTypeID = $dbRecord->getInt('weapon_type_id');
			$weapons[$weaponTypeID] = self::getWeaponType($weaponTypeID, $dbRecord);
		}
		return $weapons;
	}

	protected function __construct(int $weaponTypeID, Smr\DatabaseRecord $dbRecord) {
		$this->weaponTypeID = $weaponTypeID;
		$this->name = $dbRecord->getField('weapon_name');
		$this->raceID = $dbRecord->getInt('race_id');
		$this->cost = $dbRecord->getInt('cost');
		$this->shieldDamage = $dbRecord->getInt('shield_damage');
		$this->armourDamage = $dbRecord->getInt('armour_damage');
		$this->accuracy = $dbRecord->getInt('accuracy');
		$this->powerLevel = $dbRecord->getInt('power_level');
		$this->buyerRestriction = $dbRecord->getInt('buyer_restriction');
	}

	public function getWeaponTypeID(): int {
		return $this->weaponTypeID;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getCost(): int {
		return $this->cost;
	}

	public function getShieldDamage(): int {
		return $this->shieldDamage;
	}

	public function getArmourDamage(): int {
		return $this->armourDamage;
	}

	public function getAccuracy(): int {
		return $this->accuracy;
	}

	public function getPowerLevel(): int {
		return $this->powerLevel;
	}

	public function getBuyerRestriction(): int {
		return $this->buyerRestriction;
	}

}
