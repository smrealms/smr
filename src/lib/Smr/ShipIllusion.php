<?php declare(strict_types=1);

namespace Smr;

use SmrShipType;

/**
 * Data storage for the SmrShip::illusionShip property.
 */
class ShipIllusion {

	public function __construct(
		public readonly int $shipTypeID,
		public readonly int $attackRating,
		public readonly int $defenseRating,
	) {}

	public function getName(): string {
		return SmrShipType::get($this->shipTypeID)->getName();
	}

}
