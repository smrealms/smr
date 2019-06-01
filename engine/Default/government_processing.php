<?php

// Player has selected to become a deputy/smuggler
$location = SmrLocation::getLocation($var['LocationID']);
if ($location->isHQ()) {
	$player->setAlignment(150);
} elseif ($location->isUG()) {
	$player->setAlignment(-150);
}

forward(create_container('skeleton.php', 'current_sector.php'));
