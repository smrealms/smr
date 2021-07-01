<?php declare(strict_types=1);

$db = Smr\Database::getInstance();
$var = Smr\Session::getInstance()->getCurrentVar();

foreach ($var['account_ids'] as $account_id) {
	if (empty(Request::get('notes'))) {
		$db->write('DELETE FROM log_has_notes WHERE account_id = ' . $db->escapeNumber($account_id));
	} else {
		$db->write('REPLACE INTO log_has_notes (account_id, notes) VALUES(' . $db->escapeNumber($account_id) . ', ' . $db->escapeString(Request::get('notes')) . ')');
	}
}

$container = Page::create('skeleton.php', 'log_console_detail.php');
$container->addVar('account_ids');
$container->addVar('log_type_ids');

$container->go();
