<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$player->deletePlottedCourse();

Page::create('skeleton.php', 'current_sector.php')->go();
