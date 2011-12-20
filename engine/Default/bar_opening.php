<?php
$template->assign('Message', isset($var['message']) ? $var['message'] : '');

$winningTicket = false;
//check for winner
$db->query('SELECT prize FROM player_has_ticket WHERE game_id=' . $db->escapeNumber($player->getGameID()) . ' AND account_id=' . $db->escapeNumber($player->getAccountID()) . ' AND time = 0 LIMIT 1');
if ($db->nextRecord()) {
	$winningTicket = $db->getInt('prize');
}
$template->assign('WinningTicket',$winningTicket);

?>