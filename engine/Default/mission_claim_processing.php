<?php declare(strict_types=1);

$rewardText = $player->claimMissionReward($var['MissionID']);

forward(create_container('skeleton.php', 'current_sector.php', array('MissionMessage' => $rewardText)));
