<?php
// Common code for all the planet display pages

// If not on a planet, forward to current_sector.php
if (!$player->isLandedOnPlanet()) {
	if (USING_AJAX) {
		// Auto-click current sector with javascript to avoid issues with ajax
		// updates when the display page changes.
		$container = create_container('skeleton.php', 'current_sector.php');
		$currentSectorHREF = SmrSession::getNewHREF($container);
		$template->addJavascriptForAjax('EVAL', "location.href = '$currentSectorHREF'");
		forward($container);
	} else {
		create_error('You are not on a planet!');
	}
}

$planet = $player->getSectorPlanet();
$template->assign('ThisPlanet', $planet);
$template->assign('PageTopic', 'Planet : '.$planet->getName().' [Sector #'.$player->getSectorID().']');

require_once(get_file_loc('menu.inc'));
create_planet_menu($planet);

?>
