<?php
$variable = SmrSession::getRequestVar('variable');
$type = SmrSession::getRequestVar('type');

$db2 = new SmrMySqlDatabase();
$last_id = 0;   // history initialization

//another script for comp share
if ($type == 'comp_share') require(get_file_loc('comp_share.php'));
elseif ($type == 'all_acc') require(get_file_loc('list_all.php'));
elseif ($type == 'list') {
	//=========================================================
	// List all IPs
	//=========================================================

	if (isset($var['total']))
		$total = $var['total'];
	if (empty($total))
		$total = 0;

	//we are listing ALL IPs
	$db->query('SELECT * FROM account_has_ip ORDER BY ip, account_id LIMIT '.$total.', 1000000');
	$ip_array = array();
	$count = 0;
	//make sure we have enough but not too mant to reduce lag
	while ($db->nextRecord() && $count <= $variable) {
		$id = $db->getField('account_id');
		$ip = $db->getField('ip');
		$host = $db->getField('host');

		$total += 1;
		if ($id == $last_id && $ip == $last)
			continue;
		$ip_array[] = array('ip' => $ip, 'id' => $id, 'host' => $host);
		$last = $ip;
		$last_id = $id;
		$count++;
	}
	$container = array();
	$container['url'] = 'account_close.php';
	if ($db->nextRecord())
		$container['continue'] = 'Yes';
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.= create_table();
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th align=center>Login_name</th>');
	$PHP_OUTPUT.=('<th align=center>IP</th>');
	$PHP_OUTPUT.=('<th align=center>Host</th>');
	$PHP_OUTPUT.=('<th align=center>Match?</th>');
	$PHP_OUTPUT.=('<th align=center>Disable?</th>');
	$PHP_OUTPUT.=('<th align=center>Reason</th>');
	$PHP_OUTPUT.=('<th align=center>Closed?</th>');
	if (isset($var['last_ip']))
		$last_ip = $var['last_ip'];
	if (isset($var['last_acc']))
		$last_acc = $var['last_acc'];
	if (empty($last_ip))
		$last_ip = 0;
	if (empty($last_acc))
		$last_acc = 0;
	$db->query('SELECT * FROM account_has_ip ORDER BY ip, account_id');
	$i = 1;
	while ($i <= $count) {
		$db_ent = array_shift($ip_array);
		$db_ip = $db_ent['ip'];
		$host = $db_ent['host'];
		$account_id = $db_ent['id'];
		$acc = SmrAccount::getAccount($account_id);
		$disabled = $acc->isDisabled();
		$close_reason = $disabled ? $disabled['Reason'] : '';

		if (sizeof($ip_array) > 0) {
			//get next
			$next_ent = array_shift($ip_array);
			$next_ip = $next_ent['ip'];
			$next_id = $next_ent['id'];
			//put it back
			array_unshift($ip_array, $next_ent);
		} else {
			$next_ip = 0;
			$next_id = 0;
		}

		$PHP_OUTPUT.=('<tr><td>'.$acc->getLogin().'</td><td>'.$db_ip.'</td><td>'.$host.'</td>');
		$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$db->escapeNumber($account_id));
		if ($next_ip == $db_ip || ($db_ip == $last_ip && $last_acc != $account_id) && ($db_ip != 'unknown' || $db_ip != 'unknown, unknown')) {
			if ($db2->nextRecord())
			 	$ex = $db2->getField('reason');

			$PHP_OUTPUT.=('<td><span class="red">Yes</span></td>');
			$PHP_OUTPUT.=('<td>');
			$PHP_OUTPUT.=('<input type=checkbox');
			$PHP_OUTPUT.=(' name="disable_id[]"');
			if (isset($ex) && $ex != '')
				$PHP_OUTPUT.=(' value="'.$account_id.'">');
			elseif (!empty($close_reason))
				$PHP_OUTPUT.=(' value="'.$account_id.'">');
			else
				$PHP_OUTPUT.=(' value="'.$account_id.'" checked>');
			$PHP_OUTPUT.=('</td>');
			if ($next_ip == $db_ip)
				$reason = 'Match:'.$next_id;
			else
				$reason = 'Match:'.$last_acc;
			if (isset($ex) && $ex != '')
				$PHP_OUTPUT.=('<td><input type=text name="suspicion['.$account_id.']" value="DB Exception - '.$ex.'" id="InputFields"></td>');
			else
				$PHP_OUTPUT.=('<td><input type=text name="suspicion['.$account_id.']" value="'.$reason.'" id="InputFields"></td>');
			$PHP_OUTPUT.=('<td class="noWrap">'.$close_reason.'</td>');
		} else {
			$PHP_OUTPUT.=('<td>&nbsp;</td>');
			$PHP_OUTPUT.=('<td>');
			$PHP_OUTPUT.=('<input type=checkbox');
			$PHP_OUTPUT.=(' name="disable_id[]"');
			$PHP_OUTPUT.=(' value="'.$account_id.'">');
			$PHP_OUTPUT.=('</td>');
			$PHP_OUTPUT.=('<td><input type=text name="suspicion2['.$account_id.']" id="InputFields"></td>');
			$PHP_OUTPUT.=('<td class="noWrap">'.$close_reason.'</td>');
		}
		$PHP_OUTPUT.=('</tr>');

		//set last
		$last_ip = $db_ip;
		$last_acc = $account_id;
		$ex = '';
		$i++;
	}
	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=('<input type=hidden name=last_ip value='.$last_ip.'>');
	$PHP_OUTPUT.=('<input type=hidden name=last_acc value='.$last_acc.'>');
	$PHP_OUTPUT.=('<input type=hidden name=total value='.$total.'>');
	$PHP_OUTPUT.=('<input type=hidden name=variable value='.$variable.'>');
	$PHP_OUTPUT.=('<input type=hidden name=type value='.$type.'>');
	if (isset($var['closed_so_far']))
		$PHP_OUTPUT.=('<input type=hidden name=closed_so_far value='.$var['closed_so_far'].'>');
	$PHP_OUTPUT.=create_submit('Next Page No Disable');
	$PHP_OUTPUT.=('<br />');
	$PHP_OUTPUT.=create_submit('Disable');
	$PHP_OUTPUT.=('</form>');

} elseif ($type == 'search') {
	//=========================================================
	// Search for a specific IP
	//=========================================================

	$PHP_OUTPUT.=('<center>The following accounts have the IP address ');
	$ip = $variable;
	$host = gethostbyaddr($ip);

	if ($host == $ip)
		$host = 'unknown';
	$PHP_OUTPUT.=($ip.'. Host: '.$host.'<br /><br /><br />');
	$db->query('SELECT * FROM account_has_ip WHERE ip = '.$db->escapeString($ip).' ORDER BY account_id');
	$container = array();
	$container['url'] = 'account_close.php';
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.= create_table();
	$PHP_OUTPUT.=('<tr><th align=center>Account ID</th>');
	$PHP_OUTPUT.=('<th align=center>Login</th>');
	$PHP_OUTPUT.=('<th align=center>Time</th>');
	$PHP_OUTPUT.='<th align=center>Disable?</th>
	<th align=center>Exception?</th>
	<th align=center>Closed?</th></tr>';
	while ($db->nextRecord()) {
		$id = $db->getField('account_id');
		if ($id == $last_id)
			continue;
		$time = $db->getField('time');
		$PHP_OUTPUT.=('<tr><td>'.$id.'</td>');
		$acc = SmrAccount::getAccount($id);
		$disabled = $acc->isDisabled();
		$close_reason = $disabled ? $disabled['Reason'] : '';
		$PHP_OUTPUT.=('<td>'.$acc->getLogin().'</td><td>' . date(DATE_FULL_SHORT,$time) . '</td>');
		$PHP_OUTPUT.=('<td><input type=checkbox name=same_ip[] value='.$id.'></td>');
		$PHP_OUTPUT.=('<td>');
		$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$db->escapeNumber($id));
		if ($db2->nextRecord()) {
			$ex = $db2->getField('reason');
			$PHP_OUTPUT.=($ex);
		} else
			$PHP_OUTPUT.=('&nbsp;');
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('<td class="noWrap">'.$close_reason.'</td>');
		$PHP_OUTPUT.=('</tr>');
		$last_id = $id;
	}
	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=('<input type=hidden name=first value="first">');
	$PHP_OUTPUT.=create_submit('Disable Accounts');
	$PHP_OUTPUT.=('</form></center>');

} elseif ($type == 'account_ips') {
	//=========================================================
	// List all IPs for a specific account (id)
	//=========================================================
	if(!is_numeric($variable)) create_error('Account id must be numeric.');
	$PHP_OUTPUT.=('<center>Account '.$variable.' has had the following IPs at the following times.<br />');
	$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$db->escapeNumber($variable));
	if ($db2->nextRecord()) {
		$ex = $db2->getField('reason');
		$PHP_OUTPUT.=('This account has an exception: '.$ex);
	}
	$db2->query('SELECT * FROM account_is_closed JOIN closing_reason USING(reason_id) WHERE account_id = '.$db->escapeNumber($variable));
	if ($db2->nextRecord()) {
		$close_reason = $db2->getField('reason');
		$PHP_OUTPUT.=('This account is closed: '.$close_reason);
	}
	$PHP_OUTPUT.=('<br /><br />');
	$container = array();
	$container['url'] = 'account_close.php';
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.= create_table();
	$PHP_OUTPUT.=('<tr><th align=center>IP</th>');
	$PHP_OUTPUT.=('<th align=center>Host</th><th align=center>Time</th></tr>');
	$db->query('SELECT * FROM account_has_ip WHERE account_id = '.$db->escapeNumber($variable).' ORDER BY time');
	while ($db->nextRecord()) {
		$ip = $db->getField('ip');
		$time = $db->getField('time');
		$host = $db->getField('host');

		$PHP_OUTPUT.=('<tr><td>'.$ip.'</td><td>'.$host.'</td><td>' . date(DATE_FULL_SHORT,$time) . '</td></tr>');
	}
	$PHP_OUTPUT.=('</table>Reason:&nbsp;<input type=text name=rea value="Reason Here"><input type=hidden name=second value='.$variable.'>');
	$PHP_OUTPUT.=create_submit('Disable Account');
	$PHP_OUTPUT.=('</form></center>');

} elseif ($type == 'alliance_ips') {
	//=========================================================
	// List all IPs for a specific alliance
	//=========================================================

	list ($alliance, $game) = preg_split('/[\/]/', $variable);
	if(!is_numeric($game)||!is_numeric($alliance)) {
		create_error('Incorrect format used.');
	}
	$name = SmrAlliance::getAlliance($alliance, $game)->getAllianceName();
	$db->query('SELECT ip.* FROM account_has_ip ip JOIN player USING(account_id) WHERE game_id = ' . $db->escapeNumber($game) . ' AND alliance_id = ' . $db->escapeNumber($alliance) . ' ORDER BY ip');
	$container = create_container('account_close.php');
	$PHP_OUTPUT.=('<center>Listing all IPs for alliance '.$name.' in game with ID '.$game.'<br /><br />');
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.= create_table();
	$PHP_OUTPUT.=('<tr><th align=center>Account ID</th>');
	$PHP_OUTPUT.='<th align=center>Login</th>
	<th align=center>Time</th>
	<th align=center>IP</th>
	<th align=cneter>Host</th>
	<th align=center>Disable</th>
	<th align=center>Exception?</th>
	<th align=center>Closed?</th>
	</tr>';
	while ($db->nextRecord()) {
		$id = $db->getField('account_id');
		$time = $db->getField('time');
		$ip = $db->getField('ip');
		$host = $db->getField('host');

		if ($id == $last_id && $ip == $last_ip)
			continue;
		$acc = SmrAccount::getAccount($id);
		$disabled = $acc->isDisabled();
		$close_reason = $disabled ? $disabled['Reason'] : '';
		$PHP_OUTPUT.=('<tr><td>'.$id.'</td><td>'.$acc->getLogin().'</td><td>' . date(DATE_FULL_SHORT,$time) . '</td>');
		$PHP_OUTPUT.=('<td>'.$ip.'</td><td>'.$host.'</td><td><input type=checkbox name=same_ip[] value='.$id.'></td><td>');
		$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$db->escapeNumber($id));
		if ($db2->nextRecord()) {
			$ex = $db2->getField('reason');
			$PHP_OUTPUT.=($ex);
		} else
			$PHP_OUTPUT.=('&nbsp;');
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('<td class="noWrap">'.$close_reason.'</td>');
		$PHP_OUTPUT.=('</tr>');
		$last_ip = $ip;
		$last_id = $id;
	}
	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=create_submit('Disable Accounts');
	$PHP_OUTPUT.=('<input type=hidden name=first value="first"></form></center>');

} elseif ($type == 'wild_log') {
	//=========================================================
	// List all IPs for a wildcard login name
	//=========================================================
	$db->query('SELECT ip.* FROM account_has_ip ip JOIN account USING(account_id) WHERE login LIKE ' . $db->escapeString($variable) . ' ORDER BY login, ip');
	if ($db->getNumRows()) {
		$container = create_container('account_close.php');
		$PHP_OUTPUT.=('<center>Listing all IPs for login names LIKE '.$variable.'<br /><br />');
		$PHP_OUTPUT.=create_echo_form($container);
		$PHP_OUTPUT.= create_table();
		$PHP_OUTPUT.=('<tr><th align=center>Account ID</th>');
		$PHP_OUTPUT.='<th align=center>Login</th>
		<th align=center>Time</th>
		<th align=center>IP</th>
		<th align=center>Host</th>
		<th align=center>Disable</th>
		<th align=center>Exception?</th>
		<th align=center>Closed?</th>
		</tr>';
		while ($db->nextRecord()) {
			$id = $db->getField('account_id');
			$time = $db->getField('time');
			$ip = $db->getField('ip');
			$host = $db->getField('host');

			if ($id == $last_id && $ip == $last_ip)
				continue;
			$acc = SmrAccount::getAccount($id);
			$disabled = $acc->isDisabled();
			$close_reason = $disabled ? $disabled['Reason'] : '';
			$PHP_OUTPUT.=('<tr><td>'.$id.'</td><td>'.$acc->getLogin().'</td><td>' . date(DATE_FULL_SHORT,$time) . '</td>');
			$PHP_OUTPUT.=('<td>'.$ip.'</td><td>'.$host.'</td><td><input type=checkbox name=same_ip[] value='.$id.'></td><td>');
			$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$db->escapeNumber($id));
			if ($db2->nextRecord()) {
				$ex = $db2->getField('reason');
				$PHP_OUTPUT.=($ex);
			} else
				$PHP_OUTPUT.=('&nbsp;');
			$PHP_OUTPUT.=('</td>');
			$PHP_OUTPUT.=('<td>'.$close_reason.'</td></tr>');
			$last_ip = $ip;
			$last_id = $id;
		}
		$PHP_OUTPUT.=('</table>');
		$PHP_OUTPUT.=create_submit('Disable Accounts');
		$PHP_OUTPUT.=('<input type=hidden name=first value="first"></form></center>');

	} else
		$PHP_OUTPUT.=('No login names LIKE '.$variable.' found');

} elseif ($type == 'wild_in') {
	//=========================================================
	// List all IPs for a wildcard ingame name
	//=========================================================
	$db->query('SELECT ip.* FROM account_has_ip ip JOIN player USING(account_id) WHERE player_name LIKE ' . $db->escapeString($variable) . ' ORDER BY player_name, ip');
	if ($db->getNumRows()) {
		$container = create_container('account_close.php');
		$PHP_OUTPUT.=('<center>Listing all IPs for ingame names LIKE '.$variable.'<br /><br />');
		$PHP_OUTPUT.=create_echo_form($container);
		$PHP_OUTPUT.= create_table();
		$PHP_OUTPUT.=('<tr><th align=center>Account ID</th>');
		$PHP_OUTPUT.='<th align=center>Login</th>
		<th align=center>Time</th>
		<th align=center>IP</th>
		<th align=center>Host</th>
		<th align=center>Ingame names</th>
		<th align=center>Disable</th>
		<th align=center>Exception?</th>
		<th align=center>Closed?</th>
		</tr>';
		while ($db->nextRecord()) {
			$id = $db->getField('account_id');
			$time = $db->getField('time');
			$ip = $db->getField('ip');
			$host = $db->getField('host');

			if ($id == $last_id && $ip == $last_ip)
				continue;
			$acc = SmrAccount::getAccount($id);
			$disabled = $acc->isDisabled();
			$close_reason = $disabled ? $disabled['Reason'] : '';
			$db2->query('SELECT * FROM player WHERE account_id = '.$db2->escapeNumber($id));
			$names = array();
			while ($db2->nextRecord())
				$names[] = stripslashes($db2->getField('player_name'));
			$PHP_OUTPUT.=('<tr><td>'.$id.'</td><td>'.$acc->getLogin().'</td><td>' . date(DATE_FULL_SHORT,$time) . '</td>');
			$PHP_OUTPUT.=('<td>'.$ip.'</td><td>'.$host.'</td><td>');
			$PHP_OUTPUT .= implode(', ', $names);

			$PHP_OUTPUT.=('</td><td><input type=checkbox name=same_ip[] value='.$id.'></td><td>');
			$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$db2->escapeNumber($id));
			if ($db2->nextRecord()) {
				$ex = $db2->getField('reason');
				$PHP_OUTPUT.=($ex);
			} else
				$PHP_OUTPUT.=('&nbsp;');
			$PHP_OUTPUT.=('</td>');
			$PHP_OUTPUT.=('<td>'.$close_reason.'</td>');
			$PHP_OUTPUT.=('</tr>');
			$last_ip = $ip;
			$last_id = $id;
		}
		$PHP_OUTPUT.=('</table>');
		$PHP_OUTPUT.=create_submit('Disable Accounts');
		$PHP_OUTPUT.=('<input type=hidden name=first value="first"></form></center>');

	} else
		$PHP_OUTPUT.=('No player names LIKE '.$variable.' found');

} elseif ($type == 'compare') {
	//=========================================================
	// List all IPs for specified players
	//=========================================================

	$list = preg_split('/[,]+[\s]/', $variable);
	$db->query('SELECT ip.* FROM account_has_ip ip JOIN player USING(account_id) WHERE player_name IN (' . $db->escapeArray($list).') ORDER BY ip');
	$container = create_container('account_close.php');
	$PHP_OUTPUT.=('<center>Listing all IPs for ingame names '.$variable.'<br /><br />');
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.= create_table();
	$PHP_OUTPUT.= '<tr>
	<th align=center>Account ID</th>
	<th align=center>Login</th>
	<th align=center>Time</th>
	<th align=center>IP</th>
	<th align=center>Host</th>
	<th align=center>Ingame Names</th>
	<th align=center>Disable</th>
	<th align=center>Exception?</th>
	<th align=center>Closed?</th>
	</tr>';
	while ($db->nextRecord()) {
		$id = $db->getField('account_id');
		$time = $db->getField('time');
		$ip = $db->getField('ip');
		$host = $db->getField('host');

		if ($id == $last_id && $ip == $last_ip)
			continue;
		$acc = SmrAccount::getAccount($id);
		$disabled = $acc->isDisabled();
		$close_reason = $disabled ? $disabled['Reason'] : '';
		$db2->query('SELECT * FROM player WHERE account_id = '.$db2->escapeNumber($id));
		$names = array();
		while ($db2->nextRecord())
			$names[] = stripslashes($db2->getField('player_name'));
		$PHP_OUTPUT.=('<tr><td>'.$id.'</td><td>'.$acc->getLogin().'</td><td>' . date(DATE_FULL_SHORT,$time) . '</td>');
		$PHP_OUTPUT.=('<td>'.$ip.'</td><td>'.$host.'</td><td>');
		$PHP_OUTPUT .= implode(', ', $names);
		$PHP_OUTPUT.=('</td><td><input type=checkbox name=same_ip[] value='.$id.'></td><td>');
		$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$db2->escapeNumber($id));
		if ($db2->nextRecord()) {
			$ex = $db2->getField('reason');
			$PHP_OUTPUT.=($ex);
		} else
			$PHP_OUTPUT.=('&nbsp;');
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('<td>'.$close_reason.'</td>');
		$PHP_OUTPUT.=('</tr>');
		$last_ip = $ip;
		$last_id = $id;
	}
	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=create_submit('Disable Accounts');
	$PHP_OUTPUT.=('<input type=hidden name=first value="first"></form></center>');

} elseif ($type == 'compare_log') {
	//=========================================================
	// List all IPs for specified logins
	//=========================================================
	$list = preg_split('/[,]+[\s]/', $variable);
	$db->query('SELECT ip.* FROM account_has_ip ip JOIN account USING(account_id) WHERE login IN (' . $db->escapeArray($list) . ') ORDER BY ip');
	$container = create_container('account_close.php');
	$PHP_OUTPUT.=('<center>Listing all IPs for logins '.$variable.'<br /><br />');
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.= create_table();
	$PHP_OUTPUT.= '<tr>
	<th align=center>Account ID</th>
	<th align=center>Login</th>
	<th align=center>Time</th>
	<th align=center>IP</th>
	<th align=center>Host</th>
	<th align=center>Ingame Names</th>
	<th align=center>Disable</th>
	<th align=center>Exception?</th>
	<th align=center>Closed?</th>
	</tr>';
	while ($db->nextRecord()) {
		$id = $db->getField('account_id');
		$time = $db->getField('time');
		$ip = $db->getField('ip');
		$host = $db->getField('host');

		if ($id == $last_id && $ip == $last_ip)
			continue;
		$acc = SmrAccount::getAccount($id);
		$disabled = $acc->isDisabled();
		$close_reason = $disabled ? $disabled['Reason'] : '';
		$db2->query('SELECT * FROM player WHERE account_id = '.$db->escapeNumber($id));
		$names = array();
		while ($db2->nextRecord())
			$names[] = stripslashes($db2->getField('player_name'));
		$PHP_OUTPUT.=('<tr><td>'.$id.'</td><td>'.$acc->getLogin().'</td><td>' . date(DATE_FULL_SHORT,$time) . '</td>');
		$PHP_OUTPUT.=('<td>'.$ip.'</td><td>'.$host.'</td><td>');
		$PHP_OUTPUT .= implode(', ', $names);
		$PHP_OUTPUT.=('</td><td><input type=checkbox name=same_ip[] value='.$id.'></td><td>');
		$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$db2->escapeNumber($id));
		if ($db2->nextRecord()) {
			$ex = $db2->getField('reason');
			$PHP_OUTPUT.=($ex);
		} else
			$PHP_OUTPUT.=('&nbsp;');
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('<td>'.$close_reason.'</td>');
		$PHP_OUTPUT.=('</tr>');
		$last_ip = $ip;
		$last_id = $id;
	}
	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=create_submit('Disable Accounts');
	$PHP_OUTPUT.=('<input type=hidden name=first value="first"></form></center>');

} elseif ($type == 'wild_ip') {
	//=========================================================
	// Wildcard IP search
	//=========================================================

	$db->query('SELECT * FROM account_has_ip WHERE ip LIKE '.$db->escapeString($variable).' ORDER BY time, ip');

	$container = array();
	$container['url'] = 'account_close.php';
	$PHP_OUTPUT.=('<center>Listing all IPs LIKE '.$variable.'<br /><br />');
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.= create_table();
	$PHP_OUTPUT.='<tr>
	<th align=center>Account ID</th>
	<th align=center>Login</th>
	<th align=center>Time</th>
	<th align=center>IP</th>
	<th align=center>Host</th>
	<th align=center>Ingame Names</th>
	<th align=center>Disable</th>
	<th align=center>Exception?</th>
	<th align=center>Closed?</th>
	</tr>';
	while ($db->nextRecord()) {
		$id = $db->getField('account_id');
		$time = $db->getField('time');
		$ip = $db->getField('ip');
		$host = $db->getField('host');

		if ($id == $last_id && $ip == $last_ip)
			continue;
		$acc = SmrAccount::getAccount($id);
		$disabled = $acc->isDisabled();
		$close_reason = $disabled ? $disabled['Reason'] : '';
		$db2->query('SELECT * FROM player WHERE account_id = '.$db2->escapeNumber($id));
		$names = array();
		while ($db2->nextRecord())
			$names[] = stripslashes($db2->getField('player_name'));
		$PHP_OUTPUT.=('<tr><td>'.$id.'</td><td>'.$acc->getLogin().'</td><td>' . date(DATE_FULL_SHORT,$time) . '</td>');
		$PHP_OUTPUT.=('<td>'.$ip.'</td><td>'.$host.'</td><td>');
		$PHP_OUTPUT .= implode(', ', $names);
		$PHP_OUTPUT.=('</td><td><input type=checkbox name=same_ip[] value='.$id.'></td><td>');
		$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$db2->escapeNumber($id));
		if ($db2->nextRecord()) {
			$ex = $db2->getField('reason');
			$PHP_OUTPUT.=($ex);
		} else
			$PHP_OUTPUT.=('&nbsp;');
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('<td>'.$close_reason.'</td>');
		$PHP_OUTPUT.=('</tr>');
		$last_ip = $ip;
		$last_id = $id;
	}
	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=create_submit('Disable Accounts');
	$PHP_OUTPUT.=('<input type=hidden name=first value="first"></form></center>');

} elseif ($type == 'wild_host') {
	//=========================================================
	// Wildcard host search
	//=========================================================

	$db->query('SELECT * FROM account_has_ip WHERE host LIKE '.$db->escapeString($variable).' ORDER BY time, ip');

	$container = array();
	$container['url'] = 'account_close.php';
	$PHP_OUTPUT.=('<center>Listing all hosts LIKE '.$variable.'<br /><br />');
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.= create_table();
	$PHP_OUTPUT.='<tr>
	<th align=center>Account ID</th>
	<th align=center>Login</th>
	<th align=center>Time</th>
	<th align=center>IP</th>
	<th align=center>Host</th>
	<th align=center>Ingame Names</th>
	<th align=center>Disable</th>
	<th align=center>Exception?</th>
	<th align=center>Closed?</th>
	</tr>';
	while ($db->nextRecord()) {
		$id = $db->getField('account_id');
		$time = $db->getField('time');
		$ip = $db->getField('ip');
		$host = $db->getField('host');

		if ($id == $last_id && $ip == $last_ip)
			continue;
		$acc = SmrAccount::getAccount($id);
		$disabled = $acc->isDisabled();
		$close_reason = $disabled ? $disabled['Reason'] : '';
		$db2->query('SELECT * FROM player WHERE account_id = '.$db2->escapeNumber($id));
		$names = array();
		while ($db2->nextRecord())
			$names[] = stripslashes($db2->getField('player_name'));
		$PHP_OUTPUT.=('<tr><td>'.$id.'</td><td>'.$acc->getLogin().'</td><td>' . date(DATE_FULL_SHORT,$time) . '</td>');
		$PHP_OUTPUT.=('<td>'.$ip.'</td><td>'.$host.'</td><td>');
		$PHP_OUTPUT .= implode(', ', $names);
		$PHP_OUTPUT.=('</td><td><input type=checkbox name=same_ip[] value='.$id.'></td><td>');
		$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$db2->escapeNumber($id));
		if ($db2->nextRecord()) {
			$ex = $db2->getField('reason');
			$PHP_OUTPUT.=($ex);
		} else
			$PHP_OUTPUT.=('&nbsp;');
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('<td>'.$close_reason.'</td>');
		$PHP_OUTPUT.=('</tr>');
		$last_ip = $ip;
		$last_id = $id;
	}
	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=create_submit('Disable Accounts');
	$PHP_OUTPUT.=('<input type=hidden name=first value="first"></form></center>');

}
