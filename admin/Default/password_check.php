<?

$smarty->assign('PageTopic','PASSWORD CHECKER');

// create account object
$db2 = new SMR_DB();
$db3 = new SMR_DB();

$db->query('SELECT count(password) as pwd_count, password FROM account ' .
		   'GROUP BY password ' .
		   'HAVING pwd_count > 1 ' .
		   'ORDER BY pwd_count DESC');
if ($db->nf() > 0) {

	$PHP_OUTPUT.=create_echo_form(create_container('skeleton.php', 'password_check.php'));
	$PHP_OUTPUT.=create_submit('Select All');
	$PHP_OUTPUT.=('</form>');

	$PHP_OUTPUT.=create_echo_form(create_container('password_check_processing.php', ''));
	$PHP_OUTPUT.=('<table>');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th>Login</th>');
	$PHP_OUTPUT.=('<th>eMail</th>');
	$PHP_OUTPUT.=('<th>Action</th>');
	$PHP_OUTPUT.=('</tr>');

	while ($db->next_record()) {

		$db2->query('SELECT * FROM account WHERE password = ' . $db->escape_string($db->f('password')));
		while ($db2->next_record()) {

			$curr_account_id = $db2->f('account_id');

			$PHP_OUTPUT.=('<tr>');
			$PHP_OUTPUT.=('<td>' . $db2->f('login') . '</td>');
			$PHP_OUTPUT.=('<td>' . $db2->f('email') . '</td>');
			$PHP_OUTPUT.=('<td align="center"><input type="checkbox" name="disable_account[]" value="'.$curr_account_id.'"');

			// check if this guy is maybe already disabled
			$db3->query('SELECT * FROM account_is_closed WHERE account_id = '.$curr_account_id);
			if ($db3->nf())
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

} else
	$PHP_OUTPUT.=('No double passwords!');

?>