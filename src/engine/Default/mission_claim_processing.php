<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$rewardText = $player->claimMissionReward($var['MissionID']);

Page::create('skeleton.php', 'current_sector.php', array('MissionMessage' => $rewardText))->go();
