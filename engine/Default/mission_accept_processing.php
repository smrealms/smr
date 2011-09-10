<?php

if (count($player->getMissions()) >= 3)
	create_error('red','Error','You can only have 3 missions at a time.');


$player->addMission($var['MissionID']);

forward(create_container('skeleton.php', 'current_sector.php'));
?>