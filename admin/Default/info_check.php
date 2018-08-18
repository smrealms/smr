<?php

$template->assign('PageTopic','Checking Info');
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'info_check.php';
if (isset($number))
	$container['number'] = $number;

if (!isset($number) && !isset($var['number'])) {

	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('How many player\'s info do you need to check?<br />');
	$PHP_OUTPUT.=('<input type="number" name="number" maxlength="5" size="5" id="InputFields" class="center"><br />');
	$PHP_OUTPUT.=create_submit('Next Page');
	$PHP_OUTPUT.=('</form>');

} elseif (!isset($login)) {

	$PHP_OUTPUT.=create_echo_form($container);
	$i = 0;
	$PHP_OUTPUT.=('Enter the login names in the following boxes please.<br />');
	while ($i < $number) {

		$PHP_OUTPUT.=('<input type="text" name="login['.$i.']" maxlength="35" size="35" id="InputFields" class="center">');
		$i ++;
		$PHP_OUTPUT.=('<br /><br />');

	}
	$PHP_OUTPUT.=('<br />');
	$PHP_OUTPUT.=create_submit('Check');
	$PHP_OUTPUT.=('</form>');

} else {

	$db2 = new SmrMySqlDatabase();
	$db3 = new SmrMySqlDatabase();
	$container = array();
	$container['url'] = 'account_reopen.php';
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.= create_table();
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th align="center noWrap">Name</th>');
	$PHP_OUTPUT.=('<th align="center noWrap">Email</th>');
	$PHP_OUTPUT.=('<th align="center noWrap">Disabled Info</th>');
	$PHP_OUTPUT.=('<th algin="center noWrap">Exception Reason</th>');
	$PHP_OUTPUT.=('<th align="center noWrap">Options Box</th>');
	$PHP_OUTPUT.=('</tr>');
	foreach ($login as $name) {

		$db->query('SELECT * FROM account WHERE login = '.$db->escapeString($name));

		if ($db->nextRecord()) {

			$PHP_OUTPUT.=('<tr>');
			$login_name = $db->getField('login');
			$email = $db->getField('email');
			$id = $db->getField('account_id');
			$PHP_OUTPUT.=('<td align="center">'.$name.'</td>');
			$PHP_OUTPUT.=('<td align="center">'.$email.'</td>');
			$names = array();
			$db2->query('SELECT * FROM account_is_closed WHERE account_id = '.$db->escapeNumber($id));
			if ($db2->nextRecord())
				$continue = 'TRUE';
			else
				$continue = '0';
			$match = '';
			$match_sec = '';
			$stop = '';
			$next_arr = array();
			if ($continue)
				$PHP_OUTPUT.=('<td align="center noWrap">');
			else
				$PHP_OUTPUT.=('<td align="center">&nbsp;</td>');
			while ($continue) {

				if (isset($stop) && $stop != '') {

					$continue = '0';
					continue;

				}
				if ($continue == 'next') {

					if (isset($match_sec) && $match_sec != '')
						$isset = 'yes';
					else {

						$match_sec = $login_name;
						$isset = 'no';

					}
					$db3->query('SELECT * FROM account_is_closed WHERE suspicion = '.$db->escapeString($match_sec));
					$db2->query('SELECT * FROM account WHERE login = '.$db->escapeString($match_sec));
					if ($db3->getNumRows()) {
						while ($db3->nextRecord()) {

							//we have a match the other way
							$curr_acc = SmrAccount::getAccount($db3->getField('account_id'));
							$id = $curr_acc->getAccountID();
							$match_sec = $curr_acc->getLogin();
							if (!in_array($match_sec, $names)) {

								$continue = 'next';
								$PHP_OUTPUT.=$login_name.' is disabled matching '.$match_sec.'<br />';

							} elseif (in_array($match_sec, $next_arr)) {

								$stop = 'yes';
								continue;

							} else {

								$next_arr[] = $match_sec;

							}

					   }

					} elseif ($isset == 'yes' && !$db2->nextRecord()) {

						$PHP_OUTPUT.=('Data Error 2: '.$match_sec.' does not exist!<br />');
						$continue = '0';

					} else
						$continue = '0';

				} else {

					if (isset($match) && $match != '') {

						$curr_acc = SmrAccount::getAccountByName($match);
						$id = $curr_acc->getAccountID();

					}
					$db2->query('SELECT * FROM account_is_closed WHERE account_id = '.$db->escapeNumber($id));
					if($db2->nextRecord()) {

						$match = $db2->getField('suspicion');

						if (in_array($match, $names))
							$continue = 'next';
						else {

							$continue = 'TRUE';
							$names[] = $match;
							if ($match != $login_name)
								$PHP_OUTPUT.=($login_name.' is disabled matching '.$match.'<br />');

						}

					} else {

						$PHP_OUTPUT.=('Data Error 1: '.$match.' does not exist!<br />');
						$continue = 'next';
						$names[] = $login_name;

					}

				}
			} //end while (continue)
			$PHP_OUTPUT.=('</td>');
			$account_wanted = $db->getField('account_id');
			$PHP_OUTPUT.=('<td align=center><input type="text" name="exception['.$account_wanted.']" value="no_reason" size="30" id="InputFields"></td>');
			$PHP_OUTPUT.=('<td align="center"><input type="checkbox" name="account_id[]" value="'.$account_wanted.'"></td>');
			$PHP_OUTPUT.=('</tr>');
		} else {
			$PHP_OUTPUT.=('<tr>');
			$PHP_OUTPUT.=('<td align="center" rowspan="7">'.$name.' doesn\'t exist</td>');
			$PHP_OUTPUT.=('</tr>');
		}
	} //end foreach
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="center" rowspan=4>');
	$PHP_OUTPUT.=create_submit('Reopen and add to exceptions');
	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('<td align="center" rowspan=3>');
	$PHP_OUTPUT.=create_submit('Reopen without exception');
	$PHP_OUTPUT.=('</td></tr>');
	$PHP_OUTPUT.=('</table>');
} //end else
