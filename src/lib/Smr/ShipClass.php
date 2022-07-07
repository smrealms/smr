<?php declare(strict_types=1);

namespace Smr;

/**
 * Categorization of ship types.
 */
enum ShipClass: int {

	// Backing values are `ship_type.ship_class_id` database values
	case Hunter = 1;
	case Trader = 2;
	case Raider = 3;
	case Scout = 4;
	case Starter = 5;

}
