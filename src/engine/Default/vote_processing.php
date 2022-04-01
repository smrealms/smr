<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$account = $session->getAccount();

if ($account->getAccountID() == ACCOUNT_ID_NHL) {
	create_error('This account is not allowed to cast a vote!');
}

$db = Smr\Database::getInstance();
$db->replace('voting_results', [
	'account_id' => $db->escapeNumber($account->getAccountID()),
	'vote_id' => $db->escapeNumber($var['vote_id']),
	'option_id' => $db->escapeNumber(Smr\Request::getInt('vote')),
]);
$var['url'] = 'skeleton.php';
Page::copy($var)->go();
