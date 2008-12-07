<?php

$smarty->assign('PageTopic','Check Anonymous Accounts');

// a second db object
$db2 = new SMR_DB();

$db->query('SELECT account_has_logs.account_id as account_id, login, player_name, count(account_has_logs.account_id) as number_of_entries
			FROM account_has_logs
			NATURAL JOIN account
			NATURAL JOIN player
			GROUP BY account_has_logs.account_id');
if (!$db->nf()) {

	$PHP_OUTPUT.=create_echo_error('There are no log entries at all!');
	return;

}

while ($db->next_record()) {

	if ($account_list)
		$account_list .= ', ';
	$account_list .= $db->f('account_id');

}

// get all anon bank transactions that are logged in an array
$db->query('SELECT * FROM anon_bank_transactions WHERE account_id IN ('.$account_list.') ORDER BY anon_id');
if (!$db->nf()) {

	$PHP_OUTPUT.=create_echo_error('None of the entries in all the logfiles contains anonymous bank transaction!');
	return;

}

// variable to remember the group of anon_ids in which we currently are
$anon_id = 0;

$PHP_OUTPUT.=('Following accounts where accessed by these logged people:');
$PHP_OUTPUT.=('<p>&nbsp;</p>');
$PHP_OUTPUT.=('<p>');

while ($db->next_record()) {

	if ($anon_id != $db->f('anon_id')) {

		// if this is not the first entry we have to close previous list
		if ($anon_id > 0)
			$PHP_OUTPUT.=('</ul>');

		// set current anon_id
		$anon_id = $db->f('anon_id');

		// start topic for it
		$PHP_OUTPUT.=('Account #'.$anon_id);
		$PHP_OUTPUT.=('<ul>');

	}

	$curr_account =& SmrAccount::getAccount($db->f('account_id'));

	$transaction_id = $db->f('transaction_id');

	$db2->query('SELECT * FROM anon_bank_transactions
				 WHERE account_id = '.$curr_account->account_id.' AND
					   anon_id = '.$anon_id.' AND
					   transaction_id = '.$transaction_id);
	if ($db2->next_record())
		$text = strtolower($db2->f('transaction')) . ' ' . $db2->f('amount') . ' credits';

	$PHP_OUTPUT.=('<li>'.$curr_account->login.' '.$text.'</li>');

}
$PHP_OUTPUT.=('</ul>');
$PHP_OUTPUT.=('</p>');

$PHP_OUTPUT.=('<p>&nbsp;</p>');

$PHP_OUTPUT.=('<p>');
$PHP_OUTPUT.=create_link(create_container('skeleton.php', 'log_console.php'), '<b>&lt; Back</b>');
$PHP_OUTPUT.=('</p>');

?>