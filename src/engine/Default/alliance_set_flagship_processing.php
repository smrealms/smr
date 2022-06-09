<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();
$alliance = $player->getAlliance();

$flagshipID = Smr\Request::getInt('flagship_id');

$alliance->setFlagshipID($flagshipID);
$alliance->update();

Page::create('alliance_set_op.php')->go();
