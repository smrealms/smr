<?php declare(strict_types=1);

use Smr\Menu;

/**
 * Common code for all the planet display pages
 */
function planet_common(): void {

	$template = Smr\Template::getInstance();
	$session = Smr\Session::getInstance();
	$player = $session->getPlayer();

	if (!$player->isLandedOnPlanet()) {
		// If not on planet, they must have been kicked by another player
		create_error('You have been ejected from the planet!');
	}

	$planet = $player->getSectorPlanet();
	$template->assign('ThisPlanet', $planet);
	$template->assign('PageTopic', 'Planet : ' . $planet->getDisplayName() . ' [Sector #' . $player->getSectorID() . ']');

	Menu::planet($planet);

}
