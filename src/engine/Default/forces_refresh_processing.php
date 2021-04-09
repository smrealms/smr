<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$forces = SmrForce::getForce($player->getGameID(), $player->getSectorID(), $var['owner_id']);

$forces->updateExpire();

Page::create('skeleton.php', 'current_sector.php')->go();
