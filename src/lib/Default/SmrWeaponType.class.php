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

	public static function getWeaponType(int $weaponTypeID, MySqlDatabase $db = null) : SmrWeaponType {
		if (!isset(self::$CACHE_WEAPON_TYPES[$weaponTypeID])) {
			if (is_null($db)) {
				$db = MySqlDatabase::getInstance();
				$db->query('SELECT * FROM weapon_type WHERE weapon_type_id = ' . $db->escapeNumber($weaponTypeID));
				$db->requireRecord();
			}
			$weapon = new SmrWeaponType($weaponTypeID, $db);
			self::$CACHE_WEAPON_TYPES[$weaponTypeID] = $weapon;
		}
		return self::$CACHE_WEAPON_TYPES[$weaponTypeID];
	}

	public static function getAllWeaponTypes() : array {
		$db = MySqlDatabase::getInstance();
		$db->query('SELECT * FROM weapon_type');
		$weapons = array();
		while ($db->nextRecord()) {
			$weaponTypeID = $db->getInt('weapon_type_id');
			$weapons[$weaponTypeID] = self::getWeaponType($weaponTypeID, $db);
		}
		return $weapons;
	}

	/**
	 * Returns all weapon types that are purchasable in the given game.
	 */
	public static function getAllSoldWeaponTypes(int $gameID) : array {
		$db = MySqlDatabase::getInstance();
		$db->query('SELECT DISTINCT weapon_type.* FROM weapon_type JOIN location_sells_weapons USING (weapon_type_id) JOIN location USING (location_type_id) WHERE game_id = ' . $db->escapeNumber($gameID));
		$weapons = [];
		while ($db->nextRecord()) {
			$weaponTypeID = $db->getInt('weapon_type_id');
			$weapons[$weaponTypeID] = self::getWeaponType($weaponTypeID, $db);
		}
		return $weapons;
	}

	protected function __construct(int $weaponTypeID, MySqlDatabase $db) {
		$this->weaponTypeID = $weaponTypeID;
		$this->name = $db->getField('weapon_name');
		$this->raceID = $db->getInt('race_id');
		$this->cost = $db->getInt('cost');
		$this->shieldDamage = $db->getInt('shield_damage');
		$this->armourDamage = $db->getInt('armour_damage');
		$this->accuracy = $db->getInt('accuracy');
		$this->powerLevel = $db->getInt('power_level');
		$this->buyerRestriction = $db->getInt('buyer_restriction');
	}

	public function getWeaponTypeID() {
		return $this->weaponTypeID;
	}

	public function getName() {
		return $this->name;
	}

	public function getCost() {
		return $this->cost;
	}

	public function getShieldDamage() {
		return $this->shieldDamage;
	}

	public function getArmourDamage() {
		return $this->armourDamage;
	}

	public function getAccuracy() {
		return $this->accuracy;
	}

	public function getPowerLevel() {
		return $this->powerLevel;
	}

	public function getBuyerRestriction() {
		return $this->buyerRestriction;
	}

}
