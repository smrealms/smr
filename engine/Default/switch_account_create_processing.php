<?php

if (count($account->getLinkedAccountList()) >= SmrAccount::MAX_LINKED_ACCOUNTS) {
	create_error('Cannot create another linked account!');
}

if ($var['action'] == 'Create') {
	// Create a new multi account
	$db->query('INSERT INTO account_link_login (login_id) VALUES ('.$account->getLoginID().')');
	$newAccountID = $db->getInsertID();

	$container = create_container('skeleton.php', 'game_play.php');
	$container['switch_account_id'] = $newAccountID;

} elseif ($var['action'] == 'Link') {
	$login = $_REQUEST['multi_account_login'];
	$password = $_REQUEST['multi_account_password'];

	// Link an existing multi account
	$db->query('SELECT login_id FROM account ' .
	           'WHERE login = '.$db->escapeString($login).' AND ' .
	           'password = '.$db->escapeString(md5($password)).' LIMIT 1');
	if (!$db->nextRecord()) {
		create_error('Could not find a matching account. If you believe this is an error, please contact an admin.');
	}
	$multiLoginID = $db->getInt('login_id');

	// Sanity check: multi ID > current ID
	if ($multiLoginID <= $account->getLoginID()) {
		create_error('Multi account must be newer than main account!');
	}

	// update the account_link_login to reflect the new login association
	// For existing accounts login_id == account_id
	$db->query('UPDATE account_link_login SET login_id='.$db->escapeNumber($account->getLoginID()).' WHERE account_id='.$db->escapeNumber($multiLoginID));

	// delete the old login
	$db->query('DELETE FROM account WHERE login_id='.$db->escapeNumber($multiLoginID));


}
