<?php declare(strict_types=1);

$template->assign('PageTopic', 'Edit Account');

$games = [];
$db->query('SELECT game_id, game_name FROM game WHERE enabled = \'TRUE\' ORDER BY game_id DESC');
while ($db->nextRecord()) {
	$gameID = $db->getInt('game_id');
	$games[$gameID] = $db->getField('game_name') . ' (' . $gameID . ')';
}
$template->assign('Games', $games);
$template->assign('SearchHREF', SmrSession::getNewHREF(create_container('account_edit_search_processing.php')));

if (isset($var['errorMsg'])) {
	$template->assign('ErrorMessage', $var['errorMsg']);
}
if (isset($var['msg'])) {
	$template->assign('Message', $var['msg']);
}
