<?php declare(strict_types=1);

namespace Smr;

/**
 * Enumerates the actions that can trigger port payouts.
 */
enum PortPayoutType {

	case Loot;
	case Raze;
	case Claim;
	case Destroy;

}
