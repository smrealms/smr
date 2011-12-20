<?php
$action = $_REQUEST['action'];
if ($action == 'Yes!') {
	$player->setNewbieTurns(0);
	$player->setNewbieWarning(false);
}
if ($player->isLandedOnPlanet())
	$area = 'planet_main.php';
else
	$area = 'current_sector.php';
$account->log(LOG_TYPE_MOVEMENT, 'Player drops newbie turns.', $player->getSectorID());
forward(create_container('skeleton.php', $area));

?>