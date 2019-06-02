<?php

$template->assign('PageTopic', 'Player Blacklist');

Menu::messages();
 
if (isset($var['msg'])) {
	$template->assign('Message', $var['msg']);
}

$db->query('SELECT p.player_name, p.game_id, b.entry_id FROM player p JOIN message_blacklist b ON p.account_id = b.blacklisted_id AND b.game_id = p.game_id WHERE b.account_id=' . $db->escapeNumber($player->getAccountID()) . ' ORDER BY p.game_id, p.player_name');

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
