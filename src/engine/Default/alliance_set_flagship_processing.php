<?php declare(strict_types=1);

$flagshipID = Request::getInt('flagship_id');

$alliance = $player->getAlliance();
$alliance->setFlagshipID($flagshipID);
$alliance->update();

Page::create('skeleton.php', 'alliance_set_op.php')->go();
