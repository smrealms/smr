<?php

//future features
$skipUnusedAccs = true;
$skipClosedAccs = false;
$skipExceptions = false;

//extra db object and other vars
$db2 = new SmrMySqlDatabase();
$used = array();

//check the db and get the info we need
$db->query('SELECT * FROM multi_checking_cookie WHERE `use` = \'TRUE\'');
$container = array();
$container['url'] = 'account_close.php';
$template->assign('PageTopic','Computer Sharing');
$PHP_OUTPUT.=create_echo_form($container);
while ($db->nextRecord())
{
	//get info about linked IDs
	$associatedAccs = $db->getField('array');
	//split it into individual IDs
	$accountIDs = explode('-', $associatedAccs);
	//make sure this is good data.
	if ($accountIDs[0] != MULTI_CHECKING_COOKIE_VERSION) continue;
	//how many are they linked to?
	$rows = sizeof($accountIDs);
	$echoMainAcc = TRUE;
	$currTabAccId = $db->getField('account_id');
	//if this account was listed with another we can skip it.
	if (isset($used[$currTabAccId])) continue;
	if ($rows > 1)
	{
		if (!$skipClosedAccs)
		{
			$db2->query('SELECT * FROM account_is_closed WHERE account_id = '.$currTabAccId);
			if ($db2->nextRecord())
			{
				if ($db2->getField('reason_id') != 5) $PHP_OUTPUT.=('Closed: ' . $db2->getField('suspicion') . '.<br />');
				else continue;
			}
		}
		else continue;
		if (!$skipExceptions)
		{
			$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$currTabAccId);
			if ($db2->nextRecord()) $PHP_OUTPUT.=('Exception: ' . $db2->getField('reason') . '.<br />');
		}
		else continue;
		$PHP_OUTPUT.= create_table();
		$PHP_OUTPUT.=('<tr><th align="center">Accounts</th><th>EMail</th><th>Most Common IP</th><th>Last Login</th><th>Exception</th><th>Closed</th><th>Option</th></tr>');
		
		$db2->query('SELECT account_id, login FROM account WHERE account_id ='.$currTabAccId.($skipUnusedAccs?' AND last_login > '.(TIME-86400*30):'').' LIMIT 1');
		if ($db2->nextRecord())
			$currTabAccLogin = $db2->getField('login');
		else
			$currTabAccLogin = '[Account no longer Exists]';
		foreach ($accountIDs as $currLinkAccId)
		{
			if (!is_numeric($currLinkAccId)) continue; //rare error where user modified their own cookie.  Fixed to not allow to happen in v2.
			$db2->query('SELECT account_id, login, email, validated, last_login, (SELECT ip FROM account_has_ip WHERE account_id = account.account_id GROUP BY ip ORDER BY COUNT(ip) DESC LIMIT 1) common_ip FROM account WHERE account_id = '.$currLinkAccId.($skipUnusedAccs?' AND last_login > '.(TIME-86400*30):''));
			if ($db2->nextRecord())
				$currLinkAccLogin = $db2->getField('login');
			else continue;
			
			$PHP_OUTPUT.=('<tr class="center'.($isDisabled?' red':'').'">');
			//if ($echoMainAcc) $PHP_OUTPUT.=('<td rowspan='.$rows.' align=center>'.$currTabAccLogin.' ('.$currTabAccId.')</td>');
			$PHP_OUTPUT.='<td>'.$currLinkAccLogin.' ('.$currLinkAccId.')</td>';
			$PHP_OUTPUT.='<td'.($db2->getBoolean('validated')?'':' style="text-decoration:line-through;"').'>'.$db2->getField('email').' ('.($db2->getBoolean('validated')?'Valid':'Invalid').')</td>';
			$PHP_OUTPUT.='<td>'.$db2->getField('common_ip').'</td>';
			$PHP_OUTPUT.='<td>'.date(DATE_FULL_SHORT,$db2->getField('last_login')).')</td><td>';
			$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$currLinkAccId);
			if ($db2->nextRecord()) $PHP_OUTPUT.=$db2->getField('reason');
			else $PHP_OUTPUT.=('&nbsp;');
			$PHP_OUTPUT.=('</td><td>');
			
			$db2->query('SELECT * FROM account_is_closed WHERE account_id = '.$currLinkAccId);
			if($db2->nextRecord())
				$PHP_OUTPUT.=$db2->getField('suspicion');
			else $PHP_OUTPUT.=('&nbsp;');
			$PHP_OUTPUT.=('</td><td><input type="checkbox" name="close['.$currLinkAccId.']" value="'.$associatedAccs.'">');
			$PHP_OUTPUT.=('</td></tr>');
			$echoMainAcc = FALSE;
			$used[$currLinkAccId] = TRUE;
		}
		$PHP_OUTPUT.=('</table><br />');
	}
}

$PHP_OUTPUT.=create_submit('Close Accounts');
$PHP_OUTPUT.=('</form>');
?>