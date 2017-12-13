<?php
$action = $_REQUEST['action'];
if ($action == 'Yes!') {
	$player->setNewbieTurns(0);
	$player->setNewbieWarning(false);
}

$account->log(LOG_TYPE_MOVEMENT, 'Player drops newbie turns.', $player->getSectorID());
forward(create_container('skeleton.php', 'current_sector.php'));

?>
