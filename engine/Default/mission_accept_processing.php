<?php declare(strict_types=1);

if (count($player->getMissions()) >= 3) {
	create_error('You can only have up to 3 missions at a time.');
}

$player->addMission($var['MissionID']);

forward(create_container('skeleton.php', 'current_sector.php'));
