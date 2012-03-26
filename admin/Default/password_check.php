<?php

$template->assign('PageTopic','Password Checker');

// create account object
$db2 = new SmrMySqlDatabase();
$db3 = new SmrMySqlDatabase();

$db->query('SELECT count(password) as pwd_count, password FROM account
			GROUP BY password
			HAVING pwd_count > 1
			ORDER BY pwd_count DESC');
if ($db->getNumRows() > 0)
{
	$PHP_OUTPUT.=create_echo_form(create_container('skeleton.php', 'password_check.php'));
	$PHP_OUTPUT.=create_submit('Select All');
	$PHP_OUTPUT.=('</form>');

	$PHP_OUTPUT.=create_echo_form(create_container('password_check_processing.php', ''));
	$PHP_OUTPUT.=('<table>');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th>ID</th>');
	$PHP_OUTPUT.=('<th>Login</th>');
	$PHP_OUTPUT.=('<th>eMail</th>');
	$PHP_OUTPUT.=('<th>Action</th>');
	$PHP_OUTPUT.=('</tr>');

	while ($db->nextRecord())
	{
		$db2->query('SELECT * FROM account WHERE password = ' . $db->escape_string($db->getField('password')));
		while ($db2->nextRecord())
		{
			$curr_account_id = $db2->getField('account_id');

			$db3->query('SELECT * FROM account_is_closed WHERE account_id = '.$db->escapeNumber($curr_account_id).' LIMIT 1');
			$isDisabled = $db3->getNumRows() > 0;

			$PHP_OUTPUT.=('<tr'.($isDisabled?' class="red"':'').'>');
			$PHP_OUTPUT.=('<td>' . $db2->getField('account_id') . '</td>');
			$PHP_OUTPUT.=('<td>' . $db2->getField('login') . '</td>');
			$PHP_OUTPUT.=('<td'.($db2->getBoolean('validated')?'':' style="text-decoration:line-through;"').'>' . $db2->getField('email') . ' ('.($db2->getBoolean('validated')?'Valid':'Invalid').')</td>');
			$PHP_OUTPUT.=('<td align="center"><input type="checkbox" name="disable_account[]" value="'.$curr_account_id.'"');

			// check if this guy is maybe already disabled
			$db3->query('SELECT * FROM account_is_closed WHERE account_id = '.$db->escapeNumber($curr_account_id));
			if ($isDisabled)
				$PHP_OUTPUT.=(' checked');

			// but maybe it is preselected through this script?
			else if ($action == 'Select All')
				$PHP_OUTPUT.=(' checked');

			$PHP_OUTPUT.=('></td>');
			$PHP_OUTPUT.=('</tr>');
		}
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td colspan="3">&nbsp;</td>');
		$PHP_OUTPUT.=('</tr>');
	}

	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td colspan="2">&nbsp;</td>');
	$PHP_OUTPUT.=('<td align="center">');
	$PHP_OUTPUT.=create_submit('Disable');
	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('</tr>');
	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=('</form>');
}
else
	$PHP_OUTPUT.=('No double passwords!');

?>