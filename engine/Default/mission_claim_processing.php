<?php

if ($var['MissionID']) {
	create_error('red','Error','You can only have 3 missions at a time.');
}

$rewardText = $player->claimMissionReward($var['MissionID']);

forward(create_container('skeleton.php', 'current_sector.php', array('MissionMessage' => $rewardText)));
