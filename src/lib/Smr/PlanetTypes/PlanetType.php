<?php declare(strict_types=1);

namespace Smr\PlanetTypes;

use Exception;
use Smr\PlanetStructureType;

/**
 * Defines intrinsic properties of planet types.
 */
abstract class PlanetType {

	protected const int MAX_LANDED_UNLIMITED = 0;

	// These types are associated with database indexes and must not change
	public const int TYPE_TERRAN = 1;
	public const int TYPE_ARID = 2;
	public const int TYPE_DWARF = 3;
	public const int TYPE_DEFENSE = 4;
	public const int TYPE_PROTO = 5;

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
	 * @return array<\Smr\PlanetMenuOption>
	 */
	abstract public function menuOptions(): array;

	/** @var array<int, PlanetStructureType> */
	private array $structures;

	/**
	 * Associates the planet_type_id with the planet class.
	 * These indices must not be changed!
	 */
	public const array PLANET_TYPES = [
		self::TYPE_TERRAN => TerranPlanet::class,
		self::TYPE_ARID => AridPlanet::class,
		self::TYPE_DWARF => DwarfPlanet::class,
		self::TYPE_DEFENSE => DefenseWorld::class,
		self::TYPE_PROTO => ProtoPlanet::class,
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
	 * @return \Smr\PlanetStructureType|array<int, PlanetStructureType>
	 */
	public function structureTypes(?int $structureID = null): PlanetStructureType|array {
		if (!isset($this->structures)) {
			foreach ($this->getStructureData() as $ID => $Info) {
				$this->structures[$ID] = new PlanetStructureType($ID, $Info);
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
