<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();

if ($account->getAccountID() == ACCOUNT_ID_NHL) {
	create_error('This account is not allowed to cast a vote!');
}

$db = Smr\Database::getInstance();
$db->write('REPLACE INTO voting_results (account_id, vote_id, option_id) VALUES (' . $db->escapeNumber($account->getAccountID()) . ',' . $db->escapeNumber($var['vote_id']) . ',' . $db->escapeNumber(Request::getInt('vote')) . ')');
$var['url'] = 'skeleton.php';
Page::copy($var)->go();
