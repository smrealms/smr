<?php declare(strict_types=1);

namespace Smr;

enum BountyType: string {

	// Backing values are `bounty.type` database values
	case HQ = 'HQ';
	case UG = 'UG';

}
