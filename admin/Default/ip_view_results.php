<?php
if (isset($_REQUEST['variable']))
	SmrSession::updateVar('Variable',$_REQUEST['variable']);
$variable = $var['Variable'];
if (isset($_REQUEST['type']))
	SmrSession::updateVar('Type',$_REQUEST['type']);
$type = $var['Type'];
$db2 = new SmrMySqlDatabase();
//used to make sure we don't display deleted accounts
$del_num = 5;
$close_reason = '&nbsp;';
if (isset($var['type']))
	$type = $var['type'];
//another script for comp share
if ($type == 'comp_share') include(get_file_loc('comp_share.php'));
elseif ($type == 'all_acc') include(get_file_loc('list_all.php'));
elseif ($type == 'list') {

	if (isset($var['total']))
		$total = $var['total'];
	if (empty($total))
		$total = 0;
	if (isset($var['variable']))
		$variable = $var['variable'];
	//we are listing ALL IPs
	$db->query('SELECT account_id as acc_id, ip FROM account_has_ip ORDER BY ip, account_id LIMIT '.$total.', 1000000');
	$ip_array = array();
	$count = 0;
	//make sure we have enough but not too mant to reduce lag
	while ($db->nextRecord() && $count <= $variable) {
		$id = $db->getField('acc_id');
		$db2->query('SELECT * FROM account_is_closed WHERE account_id = '.$id.' AND reason_id = '.$del_num);
		if ($db2->nextRecord())
			continue;
		$ip = $db->getField('ip');
		list($fi,$se,$th,$fo,$crap) = split ('[.\s,]', $ip, 5);
		$ip = $fi.'.'.$se.'.'.$th.'.'.$fo;
		//$PHP_OUTPUT.=('fi='.$fi.' se='.$se.' th='.$th.' fo='.$fo.' therefore->'.$ip);
		
		$total += 1;
		if ($id == $last_id && $ip == $last)
			continue;
		$ip_array[] = $ip.'/'.$id;
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
	//for ($i=1;$i <= $count;$i++)
		$db_ent = array_shift($ip_array);
		list ($db_ip, $db_id) = split ('[/]', $db_ent);
		$account_id = $db_id;
		/*if ($last_acc == $account_id && $db_ip == $last_ip) {
			array_unshift($ip_array, $db_ip);
			continue;
		}*/
		$db2->query('SELECT login, password FROM account WHERE account_id = '.$account_id);
		$db2->nextRecord();
		$login = $db2->getField('login');
		if (sizeof($ip_array) > 0) {
			//get next
			$next_ent = array_shift($ip_array);
			list ($next_ip, $next_id) = split ('[/]', $next_ent);
			//put it back
			array_unshift($ip_array, $next_ent);
		} else {
			$next_ip = 0;
			$next_id = 0;
		}
		$db2->query('SELECT * FROM account_is_closed NATURAL JOIN closing_reason WHERE reason = \'Tagged for deletion\' AND account_id = '.$id);
		if ($db2->nextRecord())
			continue;
		$host = gethostbyaddr($db_ip);
		
		if ($host == $db_ip)
			$host = 'unknown';
		
		$PHP_OUTPUT.=('<tr><td>'.$login.'</td><td>'.$db_ip.'</td><td>'.$host.'</td>');
		$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$account_id);
		if ($next_ip == $db_ip || ($db_ip == $last_ip && $last_acc != $account_id) && ($db_ip != 'unknown' || $db_ip != 'unknown, unknown')) {
			if ($db2->nextRecord())
			 	$ex = $db2->getField('reason');
			
			$PHP_OUTPUT.=('<td><font color=red>Yes</font></td>');
			$PHP_OUTPUT.=('<td>');
			$PHP_OUTPUT.=('<input type=checkbox');
			$PHP_OUTPUT.=(' name="disable_id[]"');
			$db2->query('SELECT * FROM account_is_closed NATURAL JOIN closing_reason WHERE account_id = '.$account_id);
			if ($db2->nextRecord()) $close_reason = $db2->getField('reason');
			if (isset($ex) && $ex != '')
				$PHP_OUTPUT.=(' value="'.$account_id.'">');
			elseif (isset($close_reason) && $close_reason != '' && $close_reason != '&nbsp;')
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
			$db2->query('SELECT * FROM account_is_closed NATURAL JOIN closing_reason WHERE account_id = '.$account_id);
			//$PHP_OUTPUT.=('SELECT * FROM account_is_closed NATURAL JOIN closing_reason WHERE account_id = '.$account_id);
			if ($db2->nextRecord()) $close_reason = $db2->getField('reason');
			$PHP_OUTPUT.=('<td class="noWrap">'.$close_reason.'</td>');
		}
		$PHP_OUTPUT.=('</tr>');
		
		//set last
		$last_ip = $db_ip;
		$last_acc = $account_id;
		$ex = '';
		$close_reason = '&nbsp;';
		$i++;
	}
	$PHP_OUTPUT.=('</table>');
	$new_start = $start + $variable;
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
	
	$PHP_OUTPUT.=('<center>The following accounts have the IP address ');
	$ip = $variable;
	$host = gethostbyaddr($ip);
	
	if ($host == $ip)
		$host = 'unknown';
	$PHP_OUTPUT.=(ip.'. Host: '.$host.'<br /><br /><br />');
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
		if ($id == $last)
			continue;
		$db2->query('SELECT * FROM account_is_closed NATURAL JOIN closing_reason WHERE reason = \'Tagged for deletion\' AND account_id = '.$id);
		if ($db2->nextRecord())
			continue;		
		$time = $db->getField('time');
		$PHP_OUTPUT.=('<tr><td>'.$id.'</td>');
		$db2->query('SELECT login FROM account WHERE account_id = '.$id);
		$db2->nextRecord();
		$login = $db2->getField('login');
		$PHP_OUTPUT.=('<td>'.$login.'</td><td>' . date(DATE_FULL_SHORT,$time) . '</td>');
		$PHP_OUTPUT.=('<td><input type=checkbox name=same_ip[] value='.$id.'></td>');
		$PHP_OUTPUT.=('<td>');
		$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$id);
		if ($db2->nextRecord()) {
			$ex = $db2->getField('reason');
			$PHP_OUTPUT.=($ex);
		} else
			$PHP_OUTPUT.=('&nbsp;');
		$PHP_OUTPUT.=('</td>');
		$db2->query('SELECT * FROM account_is_closed NATURAL JOIN closing_reason WHERE account_id = '.$id);
		if ($db2->nextRecord()) $close_reason = $db2->getField('reason');
		else $close_reason = '&nbsp;';
		$PHP_OUTPUT.=('<td class="noWrap">'.$close_reason.'</td>');
		$PHP_OUTPUT.=('</tr>');
		$close_reason = '&nbsp;';
		$last = $id;
	}
	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=('<input type=hidden name=first value="'.$val.'">');
	$PHP_OUTPUT.=create_submit('Disable Accounts');
	$PHP_OUTPUT.=('</form></center>');
	
} elseif ($type == 'account_ips') {
	
	$PHP_OUTPUT.=('<center>Account '.$variable.' has had the following IPs at the following times.<br />');
	$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$variable);
	if ($db2->nextRecord()) {
		$ex = $db2->getField('reason');
		$PHP_OUTPUT.=('This account has an exception: '.$ex);
	}
	$db2->query('SELECT * FROM account_is_closed NATURAL JOIN closing_reason WHERE reason = \'Tagged for deletion\' AND account_id = '.$variable);
	if ($db2->nextRecord()) {
		$close_reason = $db2->getField('reason');
		$PHP_OUTPUT.=('This account is closed: '.$reason);
	}
	$PHP_OUTPUT.=('<br /><br />');
	$container = array();
	$container['url'] = 'account_close.php';
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.= create_table();
	$PHP_OUTPUT.=('<tr><th align=center>IP</th>');
	$PHP_OUTPUT.=('<th align=center>Host</th><th align=center>Time</th></tr>');
	$db->query('SELECT * FROM account_has_ip WHERE account_id = '.$variable.' ORDER BY time');
	while ($db->nextRecord()) {
		$ip = $db->getField('ip');
		list($fi,$se,$th,$fo,$crap) = split ('[.\s,]', $ip, 5);
		$ip = $fi.'.'.$se.'.'.$th.'.'.$fo;
		$time = $db->getField('time');
		$host = gethostbyaddr($ip);
		
		if ($host == $ip)
			$host = 'unknown';
		$PHP_OUTPUT.=('<tr><td>'.$ip.'</td><td>'.$host.'</td><td>' . date(DATE_FULL_SHORT,$time) . '</td></tr>');
	}
	$PHP_OUTPUT.=('</table>Reason:&nbsp;<input type=text name=rea value="Reason Here"><input type=hidden name=second value='.$variable.'>');
	$PHP_OUTPUT.=create_submit('Disable Account');
	$PHP_OUTPUT.=('</form></center>');
	
} elseif ($type == 'alliance_ips') {
	
	list ($alliance, $game) = split ('[/]', $variable);
	$db->query('SELECT * FROM player WHERE game_id = '.$game.' AND alliance_id = '.$alliance);
	$list = '(';
	$a = 1;
	while ($db->nextRecord()) {
		$id = $db->getField('account_id');
		if ($a > 1)
			$list .= ',';
		$list .= $id;
		$a ++;
	}
	$list .= ')';
	$db->query('SELECT * FROM account_has_ip WHERE account_id IN '.$list.' ORDER BY ip');
	$container = array();
	$container['url'] = 'account_close.php';
	$db2->query('SELECT * FROM alliance WHERE alliance_id = '.$alliance.' AND game_id = '.$game);
	$db2->nextRecord();
	$name = stripslashes($db2->getField('alliance_name'));
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
		list($fi,$se,$th,$fo,$crap) = split ('[.\s,]', $ip, 5);
		$ip = $fi.'.'.$se.'.'.$th.'.'.$fo;
		$host = gethostbyaddr($ip);
		
		if ($host == $ip)
			$host = 'unknown';
		if ($id == $last_id && $ip == $last_ip)
			continue;
		$db2->query('SELECT * FROM account_is_closed NATURAL JOIN closing_reason WHERE reason = \'Tagged for deletion\' AND account_id = '.$id);
		if ($db2->nextRecord())
			continue;
		$db2->query('SELECT * FROM account WHERE account_id = '.$id);
		$db2->nextRecord();
		$login = $db2->getField('login');
		$PHP_OUTPUT.=('<tr><td>'.$id.'</td><td>'.$login.'</td><td>' . date(DATE_FULL_SHORT,$time) . '</td>');
		$PHP_OUTPUT.=('<td>'.$ip.'</td><td>'.$host.'</td><td><input type=checkbox name=same_ip[] value='.$id.'></td><td>');
		$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$id);
		if ($db2->nextRecord()) {
			$ex = $db2->getField('reason');
			$PHP_OUTPUT.=($ex);
		} else
			$PHP_OUTPUT.=('&nbsp;');
		$PHP_OUTPUT.=('</td>');
		$db2->query('SELECT * FROM account_is_closed NATURAL JOIN closing_reason WHERE account_id = '.$id);
		if ($db2->nextRecord()) $close_reason = $db2->getField('reason');
		$PHP_OUTPUT.=('<td class="noWrap">'.$close_reason.'</td>');
		$PHP_OUTPUT.=('</tr>');
		$close_reason = '';
		$last_ip = $ip;
		$last_id = $id;
	}
	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=create_submit('Disable Accounts');
	$PHP_OUTPUT.=('<input type=hidden name=first value="first"></form></center>');
		
} elseif ($type == 'wild_log') {
	
	$db->query('SELECT * FROM account WHERE login LIKE '.$db->escapeString($variable).' ORDER BY login');
	$list = '(';
	$a = 1;
	if ($db->getNumRows()) {
		while ($db->nextRecord()) {
			$id = $db->getField('account_id');
			if ($a > 1)
				$list .= ',';
			$list .= $id;
			$a ++;
		}
		$list .= ')';
		$db->query('SELECT * FROM account_has_ip WHERE account_id IN '.$list.' ORDER BY ip');
		$container = array();
		$container['url'] = 'account_close.php';
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
			list($fi,$se,$th,$fo,$crap) = split ('[.\s,]', $ip, 5);
			$ip = $fi.'.'.$se.'.'.$th.'.'.$fo;
			$host = gethostbyaddr($ip);
		
			if ($host == $ip)
				$host = 'unknown';
			if ($id == $last_id && $ip == $last_ip)
				continue;
			$db2->query('SELECT * FROM account_is_closed NATURAL JOIN closing_reason WHERE reason = \'Tagged for deletion\' AND account_id = '.$id);
			if ($db2->nextRecord())
				continue;
			$db2->query('SELECT * FROM account WHERE account_id = '.$id);
			$db2->nextRecord();
			$login = $db2->getField('login');
			$PHP_OUTPUT.=('<tr><td>'.$id.'</td><td>'.$login.'</td><td>' . date(DATE_FULL_SHORT,$time) . '</td>');
			$PHP_OUTPUT.=('<td>'.$ip.'</td><td>'.$host.'</td><td><input type=checkbox name=same_ip[] value='.$id.'></td><td>');
			$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$id);
			if ($db2->nextRecord()) {
				$ex = $db2->getField('reason');
				$PHP_OUTPUT.=($ex);
			} else
				$PHP_OUTPUT.=('&nbsp;');
			$PHP_OUTPUT.=('</td></tr>');
			$db2->query('SELECT * FROM account_is_closed NATURAL JOIN closing_reason WHERE account_id = '.$id);
			if ($db2->nextRecord()) $close_reason = $db2->getField('reason');
			$PHP_OUTPUT.=('<td>'.$close_reason.'</td>');
			$close_reason = '&nbsp;';
			$last_ip = $ip;
			$last_id = $id;
		}
		$PHP_OUTPUT.=('</table>');
		$PHP_OUTPUT.=create_submit('Disable Accounts');
		$PHP_OUTPUT.=('<input type=hidden name=first value="first"></form></center>');
		
	} else
		$PHP_OUTPUT.=('No login names LIKE '.$variable.' found');
		
} elseif ($type == 'wild_in') {
	
	$db->query('SELECT * FROM player WHERE player_name LIKE '.$db->escapeString($variable).' ORDER BY player_name');
	$list = '(';
	$a = 1;
	if ($db->getNumRows()) {
		while ($db->nextRecord()) {
			$id = $db->getField('account_id');
			if ($a > 1)
				$list .= ',';
			$list .= $id;
			$a ++;
		}
		$list .= ')';
		$db->query('SELECT * FROM account_has_ip WHERE account_id IN '.$list.' ORDER BY ip');
		$container = array();
		$container['url'] = 'account_close.php';
		$PHP_OUTPUT.=('<center>Listing all IPs for login names LIKE '.$variable.'<br /><br />');
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
			list($fi,$se,$th,$fo,$crap) = split ('[.\s,]', $ip, 5);
			$ip = $fi.'.'.$se.'.'.$th.'.'.$fo;
			$host = gethostbyaddr($ip);
		
			if ($host == $ip)
				$host = 'unknown';
			
			if ($id == $last_id && $ip == $last_ip)
				continue;
			$db2->query('SELECT * FROM account_is_closed NATURAL JOIN closing_reason WHERE reason = \'Tagged for deletion\' AND account_id = '.$id);
			if ($db2->nextRecord())
				continue;
			$db2->query('SELECT * FROM account WHERE account_id = '.$id);
			$db2->nextRecord();
			$login = $db2->getField('login');
			$db2->query('SELECT * FROM player WHERE account_id = '.$id);
			$names = array();
			while ($db2->nextRecord())
				$names[] = stripslashes($db2->getField('player_name'));
			$PHP_OUTPUT.=('<tr><td>'.$id.'</td><td>'.$login.'</td><td>' . date(DATE_FULL_SHORT,$time) . '</td>');
			$PHP_OUTPUT.=('<td>'.$ip.'</td><td>'.$host.'</td><td>');
			$a = 1;
			foreach ($names as $echoed) {
				if ($a > 1)
					$PHP_OUTPUT.=',';
				$PHP_OUTPUT.=($echoed);
				$a ++;
			}
				
			$PHP_OUTPUT.=('</td><td>'.$ip.'</td><td><input type=checkbox name=same_ip[] value='.$id.'></td><td>');
			$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$id);
			if ($db2->nextRecord()) {
				$ex = $db2->getField('reason');
				$PHP_OUTPUT.=($ex);
			} else
				$PHP_OUTPUT.=('&nbsp;');
			$PHP_OUTPUT.=('</td>');
			$db2->query('SELECT * FROM account_is_closed NATURAL JOIN closing_reason WHERE account_id = '.$id);
			if ($db2->nextRecord()) $close_reason = $db2->getField('reason');
			$PHP_OUTPUT.=('<td>'.$close_reason.'</td>');
			$PHP_OUTPUT.=('</tr>');
			$close_reason = '&nbsp;';
			$last_ip = $ip;
			$last_id = $id;
		}
		$PHP_OUTPUT.=('</table>');
		$PHP_OUTPUT.=create_submit('Disable Accounts');
		$PHP_OUTPUT.=('<input type=hidden name=first value="first"></form></center>');
		
	} else
		$PHP_OUTPUT.=('No player names LIKE '.$variable.' found');
		
} elseif ($type == 'compare') {
	
	$p = preg_split ('/[,]+[\s]/', $variable);

	$list = '(0,';
	$a = 1;
	foreach ($p as $val) {
		if ($a > 1)
			$list .= ',';
		$list .= $db->escapeString($val);
		$a++;
	}
	$list .= ')';
	
	$db->query('SELECT * FROM player WHERE player_name IN '.$list);
	$list = '(0,';
	$a = 1;
	while ($db->nextRecord()) {
		$id = $db->getField('account_id');
		if ($a > 1)
			$list .= ',';
		$list .= $id;
		$a ++;
	}
	$list .= ')';
	$db->query('SELECT * FROM account_has_ip WHERE account_id IN '.$list.' ORDER BY ip');
	$container = array();
	$container['url'] = 'account_close.php';
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
		list($fi,$se,$th,$fo,$crap) = split ('[.\s,]', $ip, 5);
		$ip = $fi.'.'.$se.'.'.$th.'.'.$fo;
		$host = gethostbyaddr($ip);
	
		if ($host == $ip)
			$host = 'unknown';
		if ($id == $last_id && $ip == $last_ip)
			continue;
		$db2->query('SELECT * FROM account_is_closed NATURAL JOIN closing_reason WHERE reason = \'Tagged for deletion\' AND account_id = '.$id);
		if ($db2->nextRecord())
			continue;
		$db2->query('SELECT * FROM account WHERE account_id = '.$id);
		$db2->nextRecord();
		$login = $db2->getField('login');
		$db2->query('SELECT * FROM player WHERE account_id = '.$id);
			$names = array();
			while ($db2->nextRecord())
				$names[] = stripslashes($db2->getField('player_name'));
		$PHP_OUTPUT.=('<tr><td>'.$id.'</td><td>'.$login.'</td><td>' . date(DATE_FULL_SHORT,$time) . '</td>');
		$PHP_OUTPUT.=('<td>'.$ip.'</td><td>'.$host.'</td><td>');
		$a = 1;
		foreach ($names as $echoed) {
			if ($a > 1)
				$PHP_OUTPUT.=',';
			$PHP_OUTPUT.=($echoed);
			$a ++;
		}
		$PHP_OUTPUT.=('</td><td><input type=checkbox name=same_ip[] value='.$id.'></td><td>');
		$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$id);
		if ($db2->nextRecord()) {
			$ex = $db2->getField('reason');
			$PHP_OUTPUT.=($ex);
		} else
			$PHP_OUTPUT.=('&nbsp;');
		$PHP_OUTPUT.=('</td>');
		$db2->query('SELECT * FROM account_is_closed NATURAL JOIN closing_reason WHERE account_id = '.$id);
		if ($db2->nextRecord()) $close_reason = $db2->getField('reason');
		$PHP_OUTPUT.=('<td>'.$close_reason.'</td>');
		$PHP_OUTPUT.=('</tr>');
		$close_reason = '&nbsp;';
		$last_ip = $ip;
		$last_id = $id;
	}
	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=create_submit('Disable Accounts');
	$PHP_OUTPUT.=('<input type=hidden name=first value="first"></form></center>');
	
} elseif ($type == 'compare_log') {
	
	$p = preg_split ('/[,]+[\s]/', $variable);

	$list = '(';
	$a = 1;
	foreach ($p as $val) {
		if ($a > 1)
			$list .= ',';
		$list .= $db->escapeString($val);
		$a++;
	}
	$list .= ')';
	
	$db->query('SELECT * FROM account WHERE login IN '.$list);
	$list = '(';
	$a = 1;
	while ($db->nextRecord()) {
		$id = $db->getField('account_id');
		if ($a > 1)
			$list .= ',';
		$list .= $id;
		$a ++;
	}
	$list .= ')';
	$db->query('SELECT * FROM account_has_ip WHERE account_id IN '.$list.' ORDER BY ip');
	$container = array();
	$container['url'] = 'account_close.php';
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
		list($fi,$se,$th,$fo,$crap) = split ('[.\s,]', $ip, 5);
		$ip = $fi.'.'.$se.'.'.$th.'.'.$fo;
		if ($id == $last_id && $ip == $last_ip)
			continue;
		$db2->query('SELECT * FROM account_is_closed NATURAL JOIN closing_reason WHERE reason = \'Tagged for deletion\' AND account_id = '.$id);
		if ($db2->nextRecord())
			continue;
		$db2->query('SELECT * FROM account WHERE account_id = '.$id);
		$db2->nextRecord();
		$login = $db2->getField('login');
		$db2->query('SELECT * FROM player WHERE account_id = '.$id);
			$names = array();
			while ($db2->nextRecord())
				$names[] = stripslashes($db2->getField('player_name'));
		$host = gethostbyaddr($ip);
		$PHP_OUTPUT.=('<tr><td>'.$id.'</td><td>'.$login.'</td><td>' . date(DATE_FULL_SHORT,$time) . '</td>');
		$PHP_OUTPUT.=('<td>'.$ip.'</td><td>'.$host.'</td><td>');
		$a = 1;
		foreach ($names as $echoed) {
			if ($a > 1)
				$PHP_OUTPUT.=',';
			$PHP_OUTPUT.=($echoed);
			$a ++;
		}
		$PHP_OUTPUT.=('</td><td><input type=checkbox name=same_ip[] value='.$id.'></td><td>');
		$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$id);
		if ($db2->nextRecord()) {
			$ex = $db2->getField('reason');
			$PHP_OUTPUT.=($ex);
		} else
			$PHP_OUTPUT.=('&nbsp;');
		$PHP_OUTPUT.=('</td>');
		$db2->query('SELECT * FROM account_is_closed NATURAL JOIN closing_reason WHERE account_id = '.$id);
		if ($db2->nextRecord()) $close_reason = $db2->getField('reason');
		$PHP_OUTPUT.=('<td>'.$close_reason.'</td>');
		$PHP_OUTPUT.=('</tr>');
		$close_reason = '&nbsp;';
		$last_ip = $ip;
		$last_id = $id;
	}
	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=create_submit('Disable Accounts');
	$PHP_OUTPUT.=('<input type=hidden name=first value="first"></form></center>');
	
} elseif ($type == 'wild_ip') {
	
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
		list($fi,$se,$th,$fo,$crap) = split ('[.\s,]', $ip, 5);
		$ip = $fi.'.'.$se.'.'.$th.'.'.$fo;
		if ($id == $last_id && $ip == $last_ip)
			continue;
		$db2->query('SELECT * FROM account_is_closed NATURAL JOIN closing_reason WHERE reason = \'Tagged for deletion\' AND account_id = '.$id);
		if ($db2->nextRecord())
			continue;
		$db2->query('SELECT * FROM account WHERE account_id = '.$id);
		$db2->nextRecord();
		$login = $db2->getField('login');
		$db2->query('SELECT * FROM player WHERE account_id = '.$id);
			$names = array();
			while ($db2->nextRecord())
				$names[] = stripslashes($db2->getField('player_name'));
		$host = gethostbyaddr($ip);
		$PHP_OUTPUT.=('<tr><td>'.$id.'</td><td>'.$login.'</td><td>' . date(DATE_FULL_SHORT,$time) . '</td>');
		$PHP_OUTPUT.=('<td>'.$ip.'</td><td>'.$host.'</td><td>');
		$a = 1;
		foreach ($names as $echoed) {
			if ($a > 1)
				$PHP_OUTPUT.=',';
			$PHP_OUTPUT.=($echoed);
			$a ++;
		}
		$PHP_OUTPUT.=('</td><td><input type=checkbox name=same_ip[] value='.$id.'></td><td>');
		$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$id);
		if ($db2->nextRecord()) {
			$ex = $db2->getField('reason');
			$PHP_OUTPUT.=($ex);
		} else
			$PHP_OUTPUT.=('&nbsp;');
		$PHP_OUTPUT.=('</td>');
		$db2->query('SELECT * FROM account_is_closed NATURAL JOIN closing_reason WHERE account_id = '.$id);
		if ($db2->nextRecord()) $close_reason = $db2->getField('reason');
		$PHP_OUTPUT.=('<td>'.$close_reason.'</td>');
		$PHP_OUTPUT.=('</tr>');
		$close_reason = '&nbsp;';
		$last_ip = $ip;
		$last_id = $id;
	}
	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=create_submit('Disable Accounts');
	$PHP_OUTPUT.=('<input type=hidden name=first value="first"></form></center>');
	
} elseif ($type == 'wild_host') {
	
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
		list($fi,$se,$th,$fo,$crap) = split ('[.\s,]', $ip, 5);
		$ip = $fi.'.'.$se.'.'.$th.'.'.$fo;
		if ($id == $last_id && $ip == $last_ip)
			continue;
		$db2->query('SELECT * FROM account_is_closed NATURAL JOIN closing_reason WHERE reason = \'Tagged for deletion\' AND account_id = '.$id);
		if ($db2->nextRecord())
			continue;
		$db2->query('SELECT * FROM account WHERE account_id = '.$id);
		$db2->nextRecord();
		$login = $db2->getField('login');
		$db2->query('SELECT * FROM player WHERE account_id = '.$id);
			$names = array();
			while ($db2->nextRecord())
				$names[] = stripslashes($db2->getField('player_name'));
		$host = gethostbyaddr($ip);
		$PHP_OUTPUT.=('<tr><td>'.$id.'</td><td>'.$login.'</td><td>' . date(DATE_FULL_SHORT,$time) . '</td>');
		$PHP_OUTPUT.=('<td>'.$ip.'</td><td>'.$host.'</td><td>');
		$a = 1;
		foreach ($names as $echoed) {
			if ($a > 1)
				$PHP_OUTPUT.=',';
			$PHP_OUTPUT.=($echoed);
			$a ++;
		}
		$PHP_OUTPUT.=('</td><td><input type=checkbox name=same_ip[] value='.$id.'></td><td>');
		$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$id);
		if ($db2->nextRecord()) {
			$ex = $db2->getField('reason');
			$PHP_OUTPUT.=($ex);
		} else
			$PHP_OUTPUT.=('&nbsp;');
		$PHP_OUTPUT.=('</td>');
		$db2->query('SELECT * FROM account_is_closed NATURAL JOIN closing_reason WHERE account_id = '.$id);
		if ($db2->nextRecord()) $close_reason = $db2->getField('reason');
		$PHP_OUTPUT.=('<td>'.$close_reason.'</td>');
		$PHP_OUTPUT.=('</tr>');
		$close_reason = '&nbsp;';
		$last_ip = $ip;
		$last_id = $id;
	}
	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=create_submit('Disable Accounts');
	$PHP_OUTPUT.=('<input type=hidden name=first value="first"></form></center>');
	
}
?>