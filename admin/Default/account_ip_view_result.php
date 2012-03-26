<?php

$template->assign('PageTopic','Viewing IPs');
$db2 = new SmrMySqlDatabase();
//this used to come from another page and im am WAY to lazy to unindent it all :)
$container = array();
$container['url'] = 'account_close.php';
$PHP_OUTPUT.=create_echo_form($container);
if (1 == 1) {

	$ordered_ip = array();
	$db->query('SELECT * FROM account WHERE account_id > 0');
	if ($db->getNumRows()) {

		$PHP_OUTPUT.= create_table();
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<th align=center>Login_name</th>');
		$PHP_OUTPUT.=('<th align=center>IP</th>');
		$PHP_OUTPUT.=('<th align=center>Match?</th>');
		$PHP_OUTPUT.=('<th align=center>Disable?</th>');
		$PHP_OUTPUT.=('</tr>');
		while ($db->nextRecord()) {

			$curr_account =& SmrAccount::getAccount($db->getField('account_id'));
			if (is_array($curr_account->ip)) {
				$new_ip = array();
				foreach ($curr_account->ip as $ip) {

					//$PHP_OUTPUT.=('added '.$ip.' to array<br />');
					if (in_array ($ip, $new_ip)) continue;
					$new_ip[] = $ip;

				}
				$ordered_ip[$curr_account->getAccountID()] = $new_ip;
			}
		}
		//order them by IP
		asort ($ordered_ip);
		//reset to make sure we are at top
		reset ($ordered_ip);
		//ooo these ips variables keep gettin better and better :)
		$ip_numbers = array();
		$account_numers = array();
		foreach ($ordered_ip as $acc_id => $newer_ip) {

			foreach ($newer_ip as $db_ip) {

				$ip_numbers[] = $db_ip;
				$account_numbers[] = $acc_id;

			}
		}
		foreach($ip_numbers as $something)
			$amount += 1;
		reset ($ip_numbers);
		array_multisort ($ip_numbers, $account_numbers);
		$i = 0;
		while ($i < $amount) {

			$account_wanted = array_shift($account_numbers);
			$ip_wanted = array_shift($ip_numbers);

			$new_acc =& SmrAccount::getAccount($account_wanted);
			$last_acc =& SmrAccount::getAccount($last_acc_id);
			$db2->query('SELECT * FROM account_is_closed WHERE account_id = '.$db2->escapeNumber($acc_id));
			if ($db2->getNumRows() && $db_ip != $last_ip) continue;
			$PHP_OUTPUT.=('<tr>');
			$PHP_OUTPUT.=('<td align=center>'.$new_acc->getLogin().' ('.$new_acc->getAccountID().')</td>');
			$PHP_OUTPUT.=('<td align=center>'.$ip_wanted.'</td>');
			if ($ip_wanted == $last_ip && !$db2->nextRecord())
			   $PHP_OUTPUT.=('<td align=center><span class="red">MATCH w/ '.$last_acc->getLogin().'</span></td>');
			elseif ($ip_wanted == $last_ip)
				$PHP_OUTPUT.=('<td align=center><span class="red">(Already disabled) MATCH w/ '.$last_acc->getLogin().'</span></td>');
			else
				$PHP_OUTPUT.=('<td align=center>&nbsp;</td>');
			$PHP_OUTPUT.=('<td><input type="checkbox" name="account_id[]" value="'.$new_acc->getAccountID().'"></td>');
			$PHP_OUTPUT.=('</tr>');
			$i += 1;
			$last_acc_id = $new_acc->getAccountID();
			$last_ip = $ip_wanted;
		}
		$PHP_OUTPUT.=('</table>');
	}
}
$PHP_OUTPUT.=create_submit('Disable');
$PHP_OUTPUT.=('</form>');
?>