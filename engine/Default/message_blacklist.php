<?php declare(strict_types=1);

$template->assign('PageTopic', 'Player Blacklist');

Menu::messages();
 
if (isset($var['msg'])) {
	$template->assign('Message', $var['msg']);
}

$db->query('SELECT p.player_name, p.game_id, b.entry_id FROM player p JOIN message_blacklist b ON p.player_id = b.blacklisted_player_id AND b.game_id = p.game_id WHERE b.player_id=' . $db->escapeNumber($player->getPlayerID()) . ' ORDER BY p.game_id, p.player_name');

$blacklist = [];
while ($db->nextRecord()) {
		$blacklist[] = $db->getRow();
}
$template->assign('Blacklist', $blacklist);

if ($blacklist) {
	$container = create_container('message_blacklist_del.php');
	$template->assign('BlacklistDeleteHREF', SmrSession::getNewHREF($container));
}

$container = create_container('message_blacklist_add.php');
$template->assign('BlacklistAddHREF', SmrSession::getNewHREF($container));
