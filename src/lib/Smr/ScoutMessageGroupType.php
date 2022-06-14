<?php declare(strict_types=1);

namespace Smr;

/**
 * Enumeration of ways to group scout messages when viewing them.
 */
enum ScoutMessageGroupType: string {

	// Backing values must not be changed, since they map to database values.
	case Always = 'ALWAYS';
	case Auto = 'AUTO';
	case Never = 'NEVER';

}
