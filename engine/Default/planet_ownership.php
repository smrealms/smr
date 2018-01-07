<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');

// create planet object
$planet = $player->getSectorPlanet();
$template->assign('PageTopic','Planet : '.$planet->getName().' [Sector #'.$player->getSectorID().']');

require_once(get_file_loc('menu.inc'));
create_planet_menu($planet);

$container = create_container('planet_ownership_processing.php');
$template->assign('ProcessingHREF', SmrSession::getNewHREF($container));

$template->assign('Planet', $planet);
$template->assign('Player', $player);

?>
