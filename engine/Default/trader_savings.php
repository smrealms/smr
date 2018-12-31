<?php

$template->assign('PageTopic', 'Savings');

Menu::trader();

$anonAccounts = [];
$db->query('SELECT * FROM anon_bank WHERE owner_id = ' . $db->escapeNumber($player->getAccountID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
while ($db->nextRecord()) {
	$anonAccounts[] = [
		'ID' => $db->getInt('anon_id'),
		'Password' => $db->getField('password'),
	];
}
$template->assign('AnonAccounts', $anonAccounts);

require_once(get_file_loc('bar.functions.inc'));
checkForLottoWinner($player->getGameID());
$template->assign('LottoInfo', getLottoInfo($player->getGameID()));

// Number of active lotto tickets this player has
$db->query('SELECT count(*) FROM player_has_ticket WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND account_id = ' . $db->escapeNumber($player->getAccountID()) . ' AND time > 0');
$db->nextRecord();
$tickets = $db->getInt('count(*)');
$template->assign('LottoTickets', $tickets);

// Number of active lotto tickets all players have
$db->query('SELECT count(*) FROM player_has_ticket WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND time > 0');
$db->nextRecord();
$tickets_tot = $db->getInt('count(*)');
if ($tickets == 0) {
	$win_chance = 0;
} else {
	$win_chance = round(100 * $tickets / $tickets_tot, 2);
}
$template->assign('LottoWinChance', $win_chance);

// Number of winning lotto tickets this player has to claim
$db->query('SELECT count(*) FROM player_has_ticket WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND account_id = ' . $db->escapeNumber($player->getAccountID()) . ' AND time = 0');
$db->nextRecord();
$template->assign('WinningTickets', $db->getInt('count(*)'));
