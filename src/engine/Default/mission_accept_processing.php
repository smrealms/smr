<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

if (count($player->getMissions()) >= 3) {
	create_error('You can only have up to 3 missions at a time.');
}

$player->addMission($var['MissionID']);

Page::create('current_sector.php')->go();
