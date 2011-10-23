<?php

$template->assign('PageTopic','Check Anonymous Accounts');

// a second db object
$db2 = new SmrMySqlDatabase();

$db->query('SELECT account_has_logs.account_id as account_id, login, player_name, count(account_has_logs.account_id) as number_of_entries
			FROM account_has_logs
			NATURAL JOIN account
			NATURAL JOIN player
			GROUP BY account_has_logs.account_id');
if (!$db->getNumRows()) {

	$PHP_OUTPUT.=create_error('There are no log entries at all!');
	return;
}

while ($db->nextRecord()) {

	if ($account_list)
		$account_list .= ', ';
	$account_list .= $db->getField('account_id');

}

// get all anon bank transactions that are logged in an array
$db->query('SELECT * FROM anon_bank_transactions WHERE account_id IN ('.$account_list.') ORDER BY anon_id');
if (!$db->getNumRows()) {

	$PHP_OUTPUT.=create_error('None of the entries in all the log files contains anonymous bank transaction!');
	return;

}

// variable to remember the group of anon_ids in which we currently are
$anon_id = 0;

$PHP_OUTPUT.=('Following accounts where accessed by these logged people:');
$PHP_OUTPUT.=('<p>&nbsp;</p>');
$PHP_OUTPUT.=('<p>');

while ($db->nextRecord()) {

	if ($anon_id != $db->getField('anon_id')) {

		// if this is not the first entry we have to close previous list
		if ($anon_id > 0)
			$PHP_OUTPUT.=('</ul>');

		// set current anon_id
		$anon_id = $db->getField('anon_id');

		// start topic for it
		$PHP_OUTPUT.=('Account #'.$anon_id);
		$PHP_OUTPUT.=('<ul>');

	}

	$curr_account =& SmrAccount::getAccount($db->getField('account_id'));

	$transaction_id = $db->getField('transaction_id');

	$db2->query('SELECT * FROM anon_bank_transactions
				 WHERE account_id = '.$curr_account->getAccountID().' AND
					   anon_id = '.$anon_id.' AND
					   transaction_id = '.$transaction_id);
	if ($db2->nextRecord())
		$text = strtolower($db2->getField('transaction')) . ' ' . $db2->getField('amount') . ' credits';

	$PHP_OUTPUT.=('<li>'.$curr_account->login.' '.$text.'</li>');

}
$PHP_OUTPUT.=('</ul>');
$PHP_OUTPUT.=('</p>');

$PHP_OUTPUT.=('<p>&nbsp;</p>');

$PHP_OUTPUT.=('<p>');
$PHP_OUTPUT.=create_link(create_container('skeleton.php', 'log_console.php'), '<b>&lt; Back</b>');
$PHP_OUTPUT.=('</p>');

?>