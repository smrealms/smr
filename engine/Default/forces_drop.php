<?php

if (isset($var['owner_id'])) {
	$owner = SmrPlayer::getPlayer($var['owner_id'], $player->getGameID());
	$template->assign('PageTopic', 'Change ' . $owner->getPlayerName() . '\'s Forces');
	$owner_id = $var['owner_id'];
} else {
	$template->assign('PageTopic', 'Drop Forces');
	$owner_id = $player->getAccountID();
}

$forces = SmrForce::getForce($player->getGameID(), $player->getSectorID(), $owner_id);

$container = create_container('forces_drop_processing.php');
$container['owner_id'] = $owner_id;

$template->assign('Forces', $forces);
$template->assign('SubmitHREF', SmrSession::getNewHREF($container));
