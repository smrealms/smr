<?php declare(strict_types=1);

use Smr\Epoch;

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

// Note: getSectorForces is cached and also called for sector display,
// so it saves time to call it here instead of a new query.
$sectorForces = SmrForce::getSectorForces($player->getGameID(), $player->getSectorID());
$time = Epoch::time();
foreach ($sectorForces as $sectorForce) {
	if ($player->sharedForceAlliance($sectorForce->getOwner())) {
		$time += SmrForce::REFRESH_ALL_TIME_PER_STACK;
		$sectorForce->updateRefreshAll($player, $time);
	}
}

$container = Page::create('current_sector.php');
$container['showForceRefreshMessage'] = true;
$container->go();
