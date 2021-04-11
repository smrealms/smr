<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

require('planet.inc.php');

$container = Page::create('planet_ownership_processing.php');
$template->assign('ProcessingHREF', $container->href());

$template->assign('Planet', $planet);
$template->assign('Player', $player);

// Check if this player already owns a planet
$playerPlanet = $player->getPlanet();
if ($playerPlanet !== false) {
	$template->assign('PlayerPlanet', $playerPlanet->getSectorID());
}
