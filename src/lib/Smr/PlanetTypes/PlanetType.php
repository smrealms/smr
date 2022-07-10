<?php declare(strict_types=1);

namespace Smr\PlanetTypes;

use Exception;
use SmrPlanetStructureType;

/**
 * Defines intrinsic properties of planet types.
 */
abstract class PlanetType {

	protected const MAX_LANDED_UNLIMITED = 0;
	protected const DEFAULT_MENU_OPTIONS = ['CONSTRUCTION', 'DEFENSE', 'STOCKPILE', 'OWNERSHIP', 'FINANCE'];

	/**
	 * Returns the properties of all the structures this planet type can build.
	 *
	 * We could access static::STRUCTURES directly (late static binding), but
	 * that confuses static analyzers, since there is no STRUCTURES const in
	 * the base class (nor should there be).
	 *
	 * @return array<int, array<string, int>>
	 */
	abstract protected function getStructureData(): array;

	abstract public function name(): string;
	abstract public function imageLink(): string;
	abstract public function description(): string;
	abstract public function maxAttackers(): int;
	abstract public function maxLanded(): int;

	/**
	 * @return array<string>
	 */
	abstract public function menuOptions(): array;

	/** @var array<int, SmrPlanetStructureType> */
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
	 * This is the intended method to construct a PlanetType child class.
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
	 *
	 * @return SmrPlanetStructureType|array<int, SmrPlanetStructureType>
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
