<?php declare(strict_types=1);

if ($account->getAccountID() == ACCOUNT_ID_NHL) {
	create_error('This account is not allowed to cast a vote!');
}

if (!is_numeric($_REQUEST['vote'])) {
	create_error('You must choose an option.');
}
$db->query('REPLACE INTO voting_results (account_id, vote_id, option_id) VALUES (' . $db->escapeNumber($account->getAccountID()) . ',' . $db->escapeNumber($var['vote_id']) . ',' . $db->escapeNumber($_REQUEST['vote']) . ')');
$var['url'] = 'skeleton.php';
forward($var);
