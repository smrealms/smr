<?php

/**
 * Defines intrinsic properties of planet types.
 */
abstract class SmrPlanetType {
	const MAX_LANDED_UNLIMITED = 0;
	const DEFAULT_MENU_OPTIONS = ['CONSTRUCTION', 'DEFENSE', 'STOCKPILE', 'OWNERSHIP', 'FINANCE'];
	abstract public function name();
	abstract public function imageLink();
	abstract public function description();
	abstract public function maxAttackers();
	abstract public function maxLanded();

	/**
	 * Associates the planet_type_id with the planet class.
	 * These indices must not be changed!
	 */
	const PLANET_TYPES = [
		1 => 'TerranPlanet',
		2 => 'AridPlanet',
		3 => 'DwarfPlanet',
		4 => 'DefenseWorld',
		5 => 'ProtoPlanet',
	];

	/**
	 * Returns an instance of the planet type from the given $typeID.
	 * This is the intended method to construct an SmrPlanetType.
	 */
	public static function getTypeInfo($typeID) {
		if (isset(self::PLANET_TYPES[$typeID])) {
			$planetType = self::PLANET_TYPES[$typeID];
			return new $planetType;
		} else {
			throw new Exception("Planet type ID does not exist: $typeID");
		}
	}

	/**
	 * Access properties of structures that this planet type can build.
	 */
	public function structureTypes($structureID=false) {
		if (!isset($this->structures)) {
			foreach (static::STRUCTURES as $ID => $Info) {
				$this->structures[$ID] = new SmrPlanetStructureType($ID, $Info);
			}
		}
		if ($structureID === false) {
			return $this->structures;
		} elseif (isset($this->structures[$structureID])) {
			return $this->structures[$structureID];
		} else {
			throw new Exception("Structure not supported on this planet type: $structureID");
		}
	}
}

class TerranPlanet extends SmrPlanetType {
	const STRUCTURES = [
		PLANET_GENERATOR => [
			'max_amount' => 25,
			'base_time' => 10800,
			'credit_cost' => 100000,
			'exp_gain' => 90,
		],
		PLANET_HANGAR => [
			'max_amount' => 100,
			'base_time' => 21600,
			'credit_cost' => 100000,
			'exp_gain' => 180,
		],
		PLANET_TURRET => [
			'max_amount' => 10,
			'base_time' => 64800,
			'credit_cost' => 1000000,
			'exp_gain' => 540,
		],
	];
	public function name()         { return "Terran Planet"; }
	public function imageLink()    { return "images/planet1.png"; }
	public function description()  { return "A lush world, with forests, seas, sweeping meadows, and indigenous lifeforms."; }
	public function maxAttackers() { return 10; }
	public function maxLanded()    { return self::MAX_LANDED_UNLIMITED; }
	public function menuOptions()  { return self::DEFAULT_MENU_OPTIONS; }
}

class AridPlanet extends SmrPlanetType {
	const STRUCTURES = [
		PLANET_GENERATOR => [
			'max_amount' => 25,
			'base_time' => 10800,
			'credit_cost' => 100000,
			'exp_gain' => 90,
		],
		PLANET_BUNKER => [
			'max_amount' => 25,
			'base_time' => 10800,
			'credit_cost' => 50000,
			'exp_gain' => 90,
		],
		PLANET_TURRET => [
			'max_amount' => 15,
			'base_time' => 21600,
			'credit_cost' => 750000,
			'exp_gain' => 180,
		],
	];
	public function name()         { return "Arid Planet"; }
	public function imageLink()    { return "images/planet2.png"; }
	public function description()  { return "A world mostly devoid of surface water, but capable of supporting life."; }
	public function maxAttackers() { return 5; }
	public function maxLanded()    { return 5; }
	public function menuOptions()  { return ['CONSTRUCTION', 'DEFENSE', 'STOCKPILE', 'OWNERSHIP']; }
}

class DwarfPlanet extends SmrPlanetType {
	const STRUCTURES = [
		PLANET_GENERATOR => [
			'max_amount' => 10,
			'base_time' => 10800,
			'credit_cost' => 100000,
			'exp_gain' => 90,
		],
		PLANET_HANGAR => [
			'max_amount' => 85,
			'base_time' => 21600,
			'credit_cost' => 100000,
			'exp_gain' => 180,
		],
		PLANET_TURRET => [
			'max_amount' => 5,
			'base_time' => 64800,
			'credit_cost' => 1000000,
			'exp_gain' => 540,
		],
	];
	public function name()         { return "Dwarf Planet"; }
	public function imageLink()    { return "images/planet3.png"; }
	public function description()  { return "A smaller than usual planet, with no native life present."; }
	public function maxAttackers() { return 5; }
	public function maxLanded()    { return self::MAX_LANDED_UNLIMITED; }
	public function menuOptions()  { return self::DEFAULT_MENU_OPTIONS; }
}

class ProtoPlanet extends SmrPlanetType {
	const STRUCTURES = [
		PLANET_HANGAR => [
			'max_amount' => 40,
			'base_time' => 21600,
			'credit_cost' => 100000,
			'exp_gain' => 180,
		],
		PLANET_BUNKER => [
			'max_amount' => 20,
			'base_time' => 10800,
			'credit_cost' => 50000,
			'exp_gain' => 90,
		],
		PLANET_WEAPON_MOUNT => [
			'max_amount' => 20,
			'base_time' => 32400,
			'credit_cost' => 300000,
			'exp_gain' => 270,
		],
		PLANET_RADAR => [
			'max_amount' => 10,
			'base_time' => 64800,
			'credit_cost' => 1000000,
			'exp_gain' => 540,
		],
	];
	public function name()         { return "Protoplanet"; }
	public function imageLink()    { return "images/planet5.png"; }
	public function description()  { return "A developing planet, not yet able to support the infrastructure of advanced technologies."; }
	public function maxAttackers() { return 5; }
	public function maxLanded()    { return 5; }
	public function menuOptions()  { return self::DEFAULT_MENU_OPTIONS; }
}

class DefenseWorld extends SmrPlanetType {
	const STRUCTURES = [
		PLANET_GENERATOR => [
			'max_amount' => 800,
			'base_time' => 2700,
			'credit_cost' => 500,
			'exp_gain' => 9,
		],
		PLANET_HANGAR => [
			'max_amount' => 3500,
			'base_time' => 5400,
			'credit_cost' => 500,
			'exp_gain' => 18,
		],
		PLANET_TURRET => [
			'max_amount' => 550,
			'base_time' => 18200,
			'credit_cost' => 5000,
			'exp_gain' => 54,
		],
		PLANET_BUNKER => [
			'max_amount' => 500,
			'base_time' => 2500,
			'credit_cost' => 250,
			'exp_gain' => 9,
		],
	];
	public function name()         { return "Defense World"; }
	public function imageLink()    { return "images/planet4.png"; }
	public function description()  { return "A fully armed and operational battle station loaded with excessive firepower."; }
	public function maxAttackers() { return 10; }
	public function maxLanded()    { return self::MAX_LANDED_UNLIMITED; }
	public function menuOptions()  { return self::DEFAULT_MENU_OPTIONS; }
}
