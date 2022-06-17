<?php declare(strict_types=1);

namespace Smr;

/**
 * Enumerates the "Plot to Nearest" categories
 */
enum PlotGroup: string {

	// Backing value is needed for converting from user input
	case Technology = 'Technology';
	case Ships = 'Ships';
	case Weapons = 'Weapons';
	case Locations = 'Locations';
	case SellGoods = 'Sell Goods';
	case BuyGoods = 'Buy Goods';
	case Galaxies = 'Galaxies';

}
