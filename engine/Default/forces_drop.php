<?php declare(strict_types=1);

if (isset($var['owner_id'])) {
	$owner = SmrPlayer::getPlayer($var['owner_id'], $player->getGameID());
	$template->assign('PageTopic', 'Change ' . htmlentities($owner->getPlayerName()) . '\'s Forces');
	$owner_player_id = $var['owner_player_id'];
} else {
	$template->assign('PageTopic', 'Drop Forces');
	$owner_player_id = $player->getPlayerID();
}

$forces = SmrForce::getForce($player->getGameID(), $player->getSectorID(), $owner_id);

$container = create_container('forces_drop_processing.php');
$container['owner_player_id'] = $owner_player_id;

$template->assign('Forces', $forces);
$template->assign('SubmitHREF', SmrSession::getNewHREF($container));
