<?php
if (isset($var['message'])) {
	$template->assign('Message', $var['message']);
} else {
	$template->assign('Message', '<i>You enter and take a seat at the bar.
	                              The bartender looks like the helpful type.</i>');
}

$winningTicket = false;
//check for winner
$db->query('SELECT prize FROM player_has_ticket WHERE game_id=' . $db->escapeNumber($player->getGameID()) . ' AND account_id=' . $db->escapeNumber($player->getAccountID()) . ' AND time = 0 LIMIT 1');
if ($db->nextRecord()) {
	$winningTicket = $db->getInt('prize');
}
$template->assign('WinningTicket',$winningTicket);

$container = create_container('skeleton.php', 'bar_main.php');
$container['script'] = 'bar_talk_bartender.php';
$template->assign('GossipHREF', SmrSession::getNewHREF($container));
