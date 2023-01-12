<?php declare(strict_types=1);

namespace Smr;

/**
 * Enumerates menu options on planets. Backing value is the link display name.
 */
enum PlanetMenuOption: string {

	case MAIN = 'Planet Main';
	case CONSTRUCTION = 'Construction';
	case DEFENSE = 'Defense';
	case OWNERSHIP = 'Ownership';
	case STOCKPILE = 'Stockpile';
	case FINANCE = 'Financial';

}
