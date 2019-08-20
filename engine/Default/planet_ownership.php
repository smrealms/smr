<?php declare(strict_types=1);

require('planet.inc');

$container = create_container('planet_ownership_processing.php');
$template->assign('ProcessingHREF', SmrSession::getNewHREF($container));

$template->assign('Planet', $planet);
$template->assign('Player', $player);

// Check if this player already owns a planet
$playerPlanet = $player->getPlanet();
if ($playerPlanet !== false) {
	$template->assign('PlayerPlanet', $playerPlanet->getSectorID());
}
