<?php

foreach ($var['account_ids'] as $account_id) {
	if (empty($_POST['notes'])) {
		$db->query('DELETE FROM log_has_notes WHERE account_id = ' . $db->escapeNumber($account_id));
	} else {
		$db->query('REPLACE INTO log_has_notes (account_id, notes) VALUES(' . $db->escapeNumber($account_id) . ', ' . $db->escapeString($_POST['notes']) . ')');
	}
}

$container = create_container('skeleton.php', 'log_console_detail.php');
transfer('account_ids');
transfer('log_type_ids');

forward($container);
