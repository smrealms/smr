<?php declare(strict_types=1);
$action = Request::get('action');
if ($action == 'Yes!') {
	$player->setNewbieTurns(0);
	$player->setNewbieWarning(false);
}

$player->log(LOG_TYPE_MOVEMENT, 'Player drops newbie turns.');
forward(create_container('skeleton.php', 'current_sector.php'));
