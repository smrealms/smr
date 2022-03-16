<?php declare(strict_types=1);

/**
 * Defines intrinsic properties of planet types.
 */
abstract class SmrPlanetType {

	protected const MAX_LANDED_UNLIMITED = 0;
	protected const DEFAULT_MENU_OPTIONS = ['CONSTRUCTION', 'DEFENSE', 'STOCKPILE', 'OWNERSHIP', 'FINANCE'];

	/**
	 * Returns the properties of all the structures this planet type can build.
	 *
	 * We could access static::STRUCTURES directly (late static binding), but
	 * that confuses static analyzers, since there is no STRUCTURES const in
	 * the base class (nor should there be).
	 */
	abstract protected function getStructureData(): array;

	abstract public function name(): string;
	abstract public function imageLink(): string;
	abstract public function description(): string;
	abstract public function maxAttackers(): int;
	abstract public function maxLanded(): int;
	abstract public function menuOptions(): array;

	private array $structures;

	/**
	 * Associates the planet_type_id with the planet class.
	 * These indices must not be changed!
	 */
	public const PLANET_TYPES = [
		1 => TerranPlanet::class,
		2 => AridPlanet::class,
		3 => DwarfPlanet::class,
		4 => DefenseWorld::class,
		5 => ProtoPlanet::class,
	];

	/**
	 * Returns an instance of the planet type from the given $typeID.
	 * This is the intended method to construct an SmrPlanetType.
	 */
	public static function getTypeInfo(int $typeID): self {
		if (!isset(self::PLANET_TYPES[$typeID])) {
			throw new Exception("Planet type ID does not exist: $typeID");
		}
		$planetType = self::PLANET_TYPES[$typeID];
		return new $planetType();
	}

	/**
	 * Access properties of structures that this planet type can build.
	 */
	public function structureTypes(int $structureID = null): SmrPlanetStructureType|array {
		if (!isset($this->structures)) {
			foreach ($this->getStructureData() as $ID => $Info) {
				$this->structures[$ID] = new SmrPlanetStructureType($ID, $Info);
			}
		}
		if ($structureID === null) {
			return $this->structures;
		}
		if (isset($this->structures[$structureID])) {
			return $this->structures[$structureID];
		}
		throw new Exception("Structure not supported on this planet type: $structureID");
	}

}

class TerranPlanet extends SmrPlanetType {

	public const STRUCTURES = [
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
	protected function getStructureData(): array {
		return self::STRUCTURES;
	}
	public function name(): string {
		return 'Terran Planet';
	}
	public function imageLink(): string {
		return 'images/planet1.png';
	}
	public function description(): string {
		return 'A lush world, with forests, seas, sweeping meadows, and indigenous lifeforms.';
	}
	public function maxAttackers(): int {
		return 10;
	}
	public function maxLanded(): int {
		return self::MAX_LANDED_UNLIMITED;
	}
	public function menuOptions(): array {
		return self::DEFAULT_MENU_OPTIONS;
	}

}

class AridPlanet extends SmrPlanetType {

	public const STRUCTURES = [
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
	protected function getStructureData(): array {
		return self::STRUCTURES;
	}
	public function name(): string {
		return 'Arid Planet';
	}
	public function imageLink(): string {
		return 'images/planet2.png';
	}
	public function description(): string {
		return 'A world mostly devoid of surface water, but capable of supporting life.';
	}
	public function maxAttackers(): int {
		return 5;
	}
	public function maxLanded(): int {
		return 5;
	}
	public function menuOptions(): array {
		return ['CONSTRUCTION', 'DEFENSE', 'STOCKPILE', 'OWNERSHIP'];
	}

}

class DwarfPlanet extends SmrPlanetType {

	public const STRUCTURES = [
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
	protected function getStructureData(): array {
		return self::STRUCTURES;
	}
	public function name(): string {
		return 'Dwarf Planet';
	}
	public function imageLink(): string {
		return 'images/planet3.png';
	}
	public function description(): string {
		return 'A smaller than usual planet, with no native life present.';
	}
	public function maxAttackers(): int {
		return 5;
	}
	public function maxLanded(): int {
		return self::MAX_LANDED_UNLIMITED;
	}
	public function menuOptions(): array {
		return self::DEFAULT_MENU_OPTIONS;
	}

}

class ProtoPlanet extends SmrPlanetType {

	public const STRUCTURES = [
		PLANET_GENERATOR => [
			'max_amount' => 5,
			'base_time' => 10800,
			'credit_cost' => 100000,
			'exp_gain' => 90,
		],
		PLANET_HANGAR => [
			'max_amount' => 50,
			'base_time' => 21600,
			'credit_cost' => 100000,
			'exp_gain' => 180,
		],
		PLANET_BUNKER => [
			'max_amount' => 15,
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
	protected function getStructureData(): array {
		return self::STRUCTURES;
	}
	public function name(): string {
		return 'Protoplanet';
	}
	public function imageLink(): string {
		return 'images/planet5.png';
	}
	public function description(): string {
		return 'A developing planet, not yet able to support the infrastructure of advanced technologies.';
	}
	public function maxAttackers(): int {
		return 5;
	}
	public function maxLanded(): int {
		return 5;
	}
	public function menuOptions(): array {
		return self::DEFAULT_MENU_OPTIONS;
	}

}

class DefenseWorld extends SmrPlanetType {

	public const STRUCTURES = [
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
	protected function getStructureData(): array {
		return self::STRUCTURES;
	}
	public function name(): string {
		return 'Defense World';
	}
	public function imageLink(): string {
		return 'images/planet4.png';
	}
	public function description(): string {
		return 'A fully armed and operational battle station loaded with excessive firepower.';
	}
	public function maxAttackers(): int {
		return 10;
	}
	public function maxLanded(): int {
		return self::MAX_LANDED_UNLIMITED;
	}
	public function menuOptions(): array {
		return self::DEFAULT_MENU_OPTIONS;
	}

}
