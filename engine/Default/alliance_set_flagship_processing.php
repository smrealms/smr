<?php declare(strict_types=1);

$flagshipID = $_REQUEST['flagship_id'];
if (!is_numeric($flagshipID)) {
	create_error('Flagship ID must be a number.');
}

$alliance = $player->getAlliance();
$alliance->setFlagshipID($flagshipID);
$alliance->update();

forward(create_container('skeleton.php', 'alliance_set_op.php'));
