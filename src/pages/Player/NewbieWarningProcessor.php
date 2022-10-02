<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$player->setNewbieWarning(false);
Page::create('newbie_warning.php')->go();
