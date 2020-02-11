<?php declare(strict_types=1);

if ($account->getAccountID() == ACCOUNT_ID_NHL) {
	create_error('This account is not allowed to cast a vote!');
}

$db->query('REPLACE INTO voting_results (account_id, vote_id, option_id) VALUES (' . $db->escapeNumber($account->getAccountID()) . ',' . $db->escapeNumber($var['vote_id']) . ',' . $db->escapeNumber(Request::getInt('vote')) . ')');
$var['url'] = 'skeleton.php';
forward($var);
