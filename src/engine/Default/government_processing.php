<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

// Player has selected to become a deputy/smuggler
$location = SmrLocation::getLocation($var['LocationID']);
if ($location->isHQ()) {
	$player->setAlignment(150);
} elseif ($location->isUG()) {
	$player->setAlignment(-150);
}

Page::create('current_sector.php')->go();
