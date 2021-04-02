<?php declare(strict_types=1);

// Player has selected to become a deputy/smuggler
$location = SmrLocation::getLocation($var['LocationID']);
if ($location->isHQ()) {
	$player->setAlignment(150);
} elseif ($location->isUG()) {
	$player->setAlignment(-150);
}

Page::create('skeleton.php', 'current_sector.php')->go();
