<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$template->assign('PageTopic', 'Edit Account');

$games = [];
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT game_id, game_name FROM game WHERE enabled = \'TRUE\' ORDER BY game_id DESC');
foreach ($dbResult->records() as $dbRecord) {
	$gameID = $dbRecord->getInt('game_id');
	$games[$gameID] = $dbRecord->getString('game_name') . ' (' . $gameID . ')';
}
$template->assign('Games', $games);
$template->assign('SearchHREF', Page::create('admin/account_edit_search_processing.php')->href());

if (isset($var['errorMsg'])) {
	$template->assign('ErrorMessage', $var['errorMsg']);
}
if (isset($var['msg'])) {
	$template->assign('Message', $var['msg']);
}
