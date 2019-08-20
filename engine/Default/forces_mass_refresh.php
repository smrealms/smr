<?php declare(strict_types=1);

// Note: getSectorForces is cached and also called for sector display,
// so it saves time to call it here instead of a new query.
$sectorForces = SmrForce::getSectorForces($player->getGameID(), $player->getSectorID());
$time = TIME;
foreach ($sectorForces as $sectorForce) {
	if ($player->sharedForceAlliance($sectorForce->getOwner())) {
		$time += SmrForce::REFRESH_ALL_TIME_PER_STACK;
		$sectorForce->updateRefreshAll($player, $time);
	}
}

$message = '[Force Check]'; //this notifies the CS to look for info.
$container = create_container('skeleton.php', 'current_sector.php');
$container['msg'] = $message;
forward($container);
