<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$player->declineMission($var['MissionID']);

Page::create('skeleton.php', 'current_sector.php')->go();
