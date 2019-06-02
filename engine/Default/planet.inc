<?php
// Common code for all the planet display pages

// If not on a planet, forward to current_sector.php
if (!$player->isLandedOnPlanet()) {
	if (USING_AJAX) {
		// Auto-click current sector with javascript to avoid issues with ajax
		// updates when the display page changes.
		$container = create_container('skeleton.php', 'current_sector.php');
		$container['msg'] = '<span class="yellow">WARNING</span>: You have been ejected from the planet!';
		$currentSectorHREF = SmrSession::getNewHREF($container);
		// json_encode the HREF as a safety precaution
		$template->addJavascriptForAjax('EVAL', 'location.href = ' . json_encode($currentSectorHREF));
		forward($container);
	} else {
		create_error('You are not on a planet!');
	}
}

$planet = $player->getSectorPlanet();
$template->assign('ThisPlanet', $planet);
$template->assign('PageTopic', 'Planet : ' . $planet->getName() . ' [Sector #' . $player->getSectorID() . ']');

Menu::planet($planet);
