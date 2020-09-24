<?php declare(strict_types=1);

/**
 * Defines the base weapon types for ships/planets.
 */
class SmrWeaponType {

	protected static array $CACHE_WEAPON_TYPES = [];

	protected int $weaponTypeID;
	protected string $name;
	protected int $raceID;
	protected int $cost;
	protected int $shieldDamage;
	protected int $armourDamage;
	protected int $accuracy;
	protected int $powerLevel;
	protected int $buyerRestriction;

	public static function getWeaponType(int $weaponTypeID, SmrMySqlDatabase $db = null) : SmrWeaponType {
		if (!isset(self::$CACHE_WEAPON_TYPES[$weaponTypeID])) {
			if (is_null($db)) {
				$db = new SmrMySqlDatabase();
				$db->query('SELECT * FROM weapon_type WHERE weapon_type_id = ' . $db->escapeNumber($weaponTypeID));
				$db->requireRecord();
			}
			$weapon = new SmrWeaponType($weaponTypeID, $db);
			self::$CACHE_WEAPON_TYPES[$weaponTypeID] = $weapon;
		}
		return self::$CACHE_WEAPON_TYPES[$weaponTypeID];
	}

	public static function getAllWeaponTypes() : array {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT * FROM weapon_type');
		$weapons = array();
		while ($db->nextRecord()) {
			$weaponTypeID = $db->getInt('weapon_type_id');
			$weapons[$weaponTypeID] = self::getWeaponType($weaponTypeID, $db);
		}
		return $weapons;
	}

	protected function __construct(int $weaponTypeID, SmrMySqlDatabase $db) {
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

	public function getRaceID() {
		return $this->raceID;
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
