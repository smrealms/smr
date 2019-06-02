<?php

$template->assign('PageTopic', 'Place a Bounty');

require_once(get_file_loc('menu_hq.inc'));
if ($sector->hasHQ()) {
	create_hq_menu();
} else {
	create_ug_menu();
}

$container = create_container('skeleton.php', 'bounty_place_confirm.php');
transfer('LocationID');
$template->assign('SubmitHREF', SmrSession::getNewHREF($container));

$bountyPlayers = [];
$db->query('SELECT player_id, player_name FROM player JOIN account USING(account_id) WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND account_id != ' . $db->escapeNumber($player->getAccountID()) . ' ORDER BY player_name');
while ($db->nextRecord()) {
	$bountyPlayers[$db->getInt('player_id')] = $db->getField('player_name');
}
$template->assign('BountyPlayers', $bountyPlayers);
