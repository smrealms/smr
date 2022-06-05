<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$rewardText = $player->claimMissionReward($var['MissionID']);

Page::create('current_sector.php', ['MissionMessage' => $rewardText])->go();
