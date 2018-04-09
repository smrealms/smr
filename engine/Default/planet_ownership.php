<?php

include('planet.inc');

$container = create_container('planet_ownership_processing.php');
$template->assign('ProcessingHREF', SmrSession::getNewHREF($container));

$template->assign('Planet', $planet);
$template->assign('Player', $player);

// Check if this player already owns a planet
$db->query('SELECT sector_id FROM planet WHERE game_id=' . $db->escapeNumber($player->getGameID()) . ' AND owner_id=' . $db->escapeNumber($player->getAccountID()));
if ($db->nextRecord()) {
	$template->assign('PlayerPlanet', $db->getInt('sector_id'));
}
