<?php

$rewardText = $player->claimMissionReward($var['MissionID']);

forward(create_container('skeleton.php', 'current_sector.php', array('MissionMessage' => $rewardText)));
