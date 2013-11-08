<?php

require_once(get_file_loc('Research.class.inc'));

if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');

$research = new Research($player->getGameID());
$research->processAllianceResearchInProgress($player);
if($research->isPlayerResearching(null, $player)){
    $research->cancelUserResearch($player);
}

$player->setLandedOnPlanet(false);
$player->update();
$account->log(LOG_TYPE_MOVEMENT, 'Player launches from planet', $player->getSectorID());
forward(create_container('skeleton.php', 'current_sector.php'));

?>