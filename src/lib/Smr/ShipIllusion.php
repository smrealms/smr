<?php declare(strict_types=1);

namespace Smr;

/**
 * Data storage for the Ship::illusionShip property.
 */
class ShipIllusion {

	public function __construct(
		public readonly int $shipTypeID,
		public readonly int $attackRating,
		public readonly int $defenseRating,
	) {}

	public function getName(): string {
		return ShipType::get($this->shipTypeID)->getName();
	}

}
