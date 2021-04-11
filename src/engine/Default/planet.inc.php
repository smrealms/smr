<?php declare(strict_types=1);
// Common code for all the planet display pages

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

// If not on a planet, forward to current_sector.php
if (!$player->isLandedOnPlanet()) {
	if (USING_AJAX) {
		// Auto-click current sector with javascript to avoid issues with ajax
		// updates when the display page changes.
		$container = Page::create('skeleton.php', 'current_sector.php');
		$container['msg'] = '<span class="yellow">WARNING</span>: You have been ejected from the planet!';
		$currentSectorHREF = $container->href();
		// json_encode the HREF as a safety precaution
		$template->addJavascriptForAjax('EVAL', 'location.href = ' . json_encode($currentSectorHREF));
		$container->go();
	} else {
		create_error('You are not on a planet!');
	}
}

$planet = $player->getSectorPlanet();
$template->assign('ThisPlanet', $planet);
$template->assign('PageTopic', 'Planet : ' . $planet->getDisplayName() . ' [Sector #' . $player->getSectorID() . ']');

Menu::planet($planet);
