<?php declare(strict_types=1);

$flagshipID = Request::getInt('flagship_id');

$alliance = $player->getAlliance();
$alliance->setFlagshipID($flagshipID);
$alliance->update();

forward(create_container('skeleton.php', 'alliance_set_op.php'));
