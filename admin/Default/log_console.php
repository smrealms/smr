<?php

$template->assign('PageTopic','Log Console');

$PHP_OUTPUT.=('<p>Choose the log files you wish to view or delete!<br />Don\'t keep unnecessary data!</p>');

$db->query('SELECT account_has_logs.account_id as account_id, login, player_name, count(account_has_logs.account_id) as number_of_entries
			FROM account_has_logs
			NATURAL JOIN account
			NATURAL JOIN player
			GROUP BY account_has_logs.account_id');
if ($db->getNumRows()) {

	// a second db object
	$db2 = new SmrMySqlDatabase();

	$PHP_OUTPUT.=create_echo_form(create_container('skeleton.php', 'log_console_detail.php'));

	$PHP_OUTPUT.=('<table border="0" class="standard" cellspacing="0" cellpadding="5">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th>Login</th>');
	$PHP_OUTPUT.=('<th>Player Name</th>');
	$PHP_OUTPUT.=('<th>Entries</th>');
	$PHP_OUTPUT.=('<th>Action</th>');
	$PHP_OUTPUT.=('<th>Notes</th>');
	$PHP_OUTPUT.=('</tr>');

	while ($db->nextRecord()) {

		$account_id			= $db->getField('account_id');
		$login				= $db->getField('login');
		$player_name		= stripslashes($db->getField('player_name'));
		$number_of_entries	= $db->getField('number_of_entries');

		if (is_array($var['account_ids']) && in_array($account_id, $var['account_ids']))
			$checked = ' checked';
		else
			$checked = '';

		// put hidden fields in for log type to have all fields selected on next page.
		$db2->query('SELECT * FROM log_type');
		while ($db2->nextRecord())
			$PHP_OUTPUT.=('<input type="hidden" name="log_type_ids[' . $db2->getField('log_type_id') . ']" value="1">');

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td valign="top">'.$login.'</td>');
		$PHP_OUTPUT.=('<td valign="top">'.$player_name.'</td>');
		$PHP_OUTPUT.=('<td valign="top" align="center">'.$number_of_entries.'</td>');
		$PHP_OUTPUT.=('<td valign="middle" align="center"><input type="checkbox" name="account_ids[]" value="'.$account_id.'"'.$checked.'></td>');

		$db2->query('SELECT * FROM log_has_notes WHERE account_id = '.$account_id);
		if ($db2->nextRecord())
			$PHP_OUTPUT.=('<td>' . nl2br($db2->getField('notes')) . '</td>');
		else
			$PHP_OUTPUT.=('<td>&nbsp;</td>');
		$PHP_OUTPUT.=('</tr>');

	}

	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td colspan="3">&nbsp;</td>');
	$PHP_OUTPUT.=('<td>');
	$PHP_OUTPUT.=create_submit('View');
	$PHP_OUTPUT.=('&nbsp;&nbsp;');
	$PHP_OUTPUT.=create_submit('Delete');
	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('<td>&nbsp;</td>');
	$PHP_OUTPUT.=('</tr>');
	$PHP_OUTPUT.=('</table>');

	$PHP_OUTPUT.=('</form>');

	$PHP_OUTPUT.=('<p>&nbsp;</p>');

	$PHP_OUTPUT.=('<p>');
	$PHP_OUTPUT.=('Check for:');
	$PHP_OUTPUT.=('<ul>');
	$PHP_OUTPUT.=create_link(create_container('skeleton.php', 'log_anonymous_account.php'), '<li>Anonymous Account access</li>');
	$PHP_OUTPUT.=('</ul>');
	$PHP_OUTPUT.=('</p>');

} else
	$PHP_OUTPUT.=('There are no log entries at all!');

?>