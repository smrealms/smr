<?php declare(strict_types=1);

namespace Smr;

/**
 * Enumerates the types of combat log pages
 */
enum CombatLogType {

	case Personal;
	case Alliance;
	case Force;
	case Port;
	case Planet;
	case Saved;

}
