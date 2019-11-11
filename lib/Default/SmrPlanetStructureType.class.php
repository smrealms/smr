<?php

/**
 * Defines intrinsic properties of planetary structure types.
 */
class SmrPlanetStructureType {

	public function __construct($ID, $planetTypeInfo) {
		$this->ID = $ID;
		$this->planetTypeInfo = $planetTypeInfo;
	}

	/**
	 * Trade goods required to build each type of structure.
	 */
	const GOODS = [
		PLANET_GENERATOR => [
			GOODS_WOOD => 20,
			GOODS_ORE => 15,
			GOODS_MACHINERY => 35,
			GOODS_COMPUTERS => 5,
		],
		PLANET_HANGAR => [
			GOODS_WOOD => 25,
			GOODS_FOOD => 10,
			GOODS_ORE => 25,
		],
		PLANET_TURRET => [
			GOODS_FOOD => 25,
			GOODS_ORE => 10,
			GOODS_MACHINERY => 25,
			GOODS_COMPUTERS => 10,
		],
		PLANET_BUNKER => [
			GOODS_WOOD => 20,
			GOODS_FOOD => 35,
			GOODS_CIRCUITRY => 15,
		],
		PLANET_WEAPON_MOUNT => [
			GOODS_WOOD => 20,
			GOODS_FOOD => 15,
			GOODS_MACHINERY => 10,
			GOODS_SLAVES => 5,
		],
		PLANET_RADAR => [
			GOODS_WOOD => 100,
			GOODS_MACHINERY => 75,
			GOODS_CIRCUITRY => 150,
		],
	];

	/**
	 * Information to display about each structure type.
	 */
	private const INFO = [
		PLANET_GENERATOR => [
			'name' => 'Generator',
			'image' => 'generator.png',
			'tooltip' => 'Generators protect a planet with shields. Each generator can hold ' . PLANET_GENERATOR_SHIELDS . ' shields.',
			'summary' => 'Increases planet\'s maximum shield capacity by ' . PLANET_GENERATOR_SHIELDS . ' shields',
		],
		PLANET_HANGAR => [
			'name' => 'Hangar',
			'image' => 'hangar.png',
			'tooltip' => 'Hangars house and launch combat drones. Each hangar holds ' . PLANET_HANGAR_DRONES . ' drones.',
			'summary' => 'Increases planet\'s maximum drone capacity by ' . PLANET_HANGAR_DRONES . ' drones',
		],
		PLANET_BUNKER => [
			'name' => 'Bunker',
			'image' => 'bunker.png',
			'tooltip' => 'Bunkers are defensive structures with reinforced armour. Each bunker holds ' . PLANET_BUNKER_ARMOUR . ' units of armour.',
			'summary' => 'Increases planet\'s maximum armour capacity by ' . PLANET_BUNKER_ARMOUR . ' armour',
		],
		PLANET_TURRET => [
			'name' => 'Turret',
			'image' => 'turret.png',
			'tooltip' => 'Turrets fire heavy laser beams. They can destroy either 250 shields or 250 armour.',
			'summary' => 'Builds a turret capable of dealing 250 damage to enemy ships when fired on',
		],
		PLANET_WEAPON_MOUNT => [
			'name' => 'Weapon Mount',
			'image' => 'weapon_mount.png',
			'tooltip' => 'Weapon mounts can be fitted with ship weapons.',
			'summary' => 'Builds a weapon mount capable of being retrofitted with ship weapons',
		],
		PLANET_RADAR => [
			'name' => 'Radar Station',
			'image' => 'radar.png',
			'tooltip' => 'Radar stations track hostile ships. Each station makes mounted weapons 5% more accurate.',
			'summary' => 'Builds a radar that tracks hostile ships, making weapons 5% more accurate',
			'hardware_cost' => [HARDWARE_SCANNER],
		],
	];

	/**
	 * Return a list of possible planet structure types
	 */
	static function getTypes() {
		return array_keys(self::INFO);
	}

	// Functions that do not require the planet type to be known
	public function structureID() { return $this->ID; }
	public function name() { return self::INFO[$this->ID]['name']; }
	public function image() { return self::INFO[$this->ID]['image']; }
	public function tooltip() { return self::INFO[$this->ID]['tooltip']; }
	public function summary() { return self::INFO[$this->ID]['summary']; }
	public function goods() { return self::GOODS[$this->ID]; }
	public function hardwareCost() {
		if (isset(self::INFO[$this->ID]['hardware_cost'])) {
			return self::INFO[$this->ID]['hardware_cost'];
		} else {
			return [];
		}
	}

	// Functions that require the planet type to be known
	public function maxAmount() { return $this->planetTypeInfo['max_amount']; }
	public function creditCost() { return $this->planetTypeInfo['credit_cost']; }
	public function baseTime() { return $this->planetTypeInfo['base_time']; }
	public function expGain() { return $this->planetTypeInfo['exp_gain']; }
}
