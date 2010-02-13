<?php

$template->assign('PageTopic','Edit Account');

if(isset($_REQUEST['account_id']))
	SmrSession::updateVar('account_id',$_REQUEST['account_id']);
if(isset($_REQUEST['login']))
	SmrSession::updateVar('login',$_REQUEST['login']);
if(isset($_REQUEST['val_code']))
	SmrSession::updateVar('val_code',$_REQUEST['val_code']);
if(isset($_REQUEST['email']))
	SmrSession::updateVar('email',$_REQUEST['email']);
if(isset($_REQUEST['hofname']))
	SmrSession::updateVar('hofname',$_REQUEST['hofname']);
if(isset($_REQUEST['player_name']))
	SmrSession::updateVar('player_name',$_REQUEST['player_name']);

$account_id = $var['account_id'];
$login = $var['login'];
$val_code = $var['val_code'];
$email = $var['email'];
$hofName = $var['hofname'];
$player_name = $var['player_name'];

if (empty($account_id))
	$account_id = 0;

// create account object
$curr_account = false;

if (!empty($player_name) && !is_array($player_name)) {

	$db->query('SELECT * FROM player ' .
			   'WHERE player_name = ' . $db->escape_string($player_name));
	if ($db->nextRecord())
		$account_id = $db->getField('account_id');
	else {
		$db->query('SELECT * FROM player ' .
				   'WHERE player_name LIKE ' . $db->escape_string($player_name));
		if ($db->nextRecord())
			$account_id = $db->getField('account_id');
	}
}

// get account from db
$db->query('SELECT account_id FROM account WHERE account_id = '.$db->escapeNumber($account_id).' OR ' .
									   'login LIKE ' . $db->escape_string($login) . ' OR ' .
									   'email LIKE ' . $db->escape_string($email) . ' OR ' .
									   'hof_name LIKE ' . $db->escapeString($hofName) . ' OR ' .
									   'validation_code LIKE ' . $db->escape_string($val_code));
if ($db->nextRecord())
	$curr_account =& SmrAccount::getAccount($db->getField('account_id'));

$container = array();

if (!$curr_account) {

	$container['url']			= 'skeleton.php';
	$container['body']			= 'account_edit.php';

} else {

	$container['url']			= 'account_edit_processing.php';
	$container['account_id']	= $curr_account->account_id;

}

$PHP_OUTPUT.=create_form_parameter($container, 'name="form_acc"');
$PHP_OUTPUT.=('<p>');
$PHP_OUTPUT.=('<table cellpadding="3" border="0">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="right" class="bold">Account ID:</td>');
if (!empty($curr_account->account_id))
	$PHP_OUTPUT.=('<td>'.$curr_account->account_id.'</td>');
else
	$PHP_OUTPUT.=('<td><input type="text" name="account_id" id="InputFields" size="5"></td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="right" class="bold">Login:</td>');
if (!empty($curr_account->login))
	$PHP_OUTPUT.=('<td>'.$curr_account->login.'</td>');
else
	$PHP_OUTPUT.=('<td><input type="text" name="login" id="InputFields" size="20"></td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="right" class="bold">Validation Code:</td>');
if (!empty($curr_account->validation_code))
	$PHP_OUTPUT.=('<td>'.$curr_account->validation_code.'</td>');
else
	$PHP_OUTPUT.=('<td><input type="text" name="val_code" id="InputFields" size="20"></td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="right" class="bold">Email:</td>');
if (!empty($curr_account->email))
	$PHP_OUTPUT.=('<td>'.$curr_account->email.'</td>');
else
	$PHP_OUTPUT.=('<td><input type="text" name="email" id="InputFields" size="20"></td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="right" class="bold">HoF Name:</td>');
if (!empty($curr_account) && $curr_account->getHofName())
	$PHP_OUTPUT.=('<td>'.$curr_account->getHofName().'</td>');
else
	$PHP_OUTPUT.=('<td><input type="text" name="hofname" id="InputFields" size="20"></td>');
$PHP_OUTPUT.=('</tr>');

if (!empty($curr_account->email)) {
	//ban points go here
	$points = $curr_account->getPoints();
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right" class="bold">Points:</td>');
	$PHP_OUTPUT.=('<td>'.$points.'</td>');
	$PHP_OUTPUT.=('</tr>');
}

$PHP_OUTPUT.=('<tr><td colspan="2">&nbsp;</td></tr>');

if ($curr_account && $curr_account->account_id != 0) {

	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right" valign="top" class="bold">Player:</td>');
	$db->query('SELECT * FROM player WHERE account_id = '.$curr_account->account_id);
	if ($db->getNumRows()) {

		$PHP_OUTPUT.=('<td>');
		$PHP_OUTPUT.=('<table>');
		while ($db->nextRecord()) {

			$game_id = $db->getField('game_id');
			$curr_player =& SmrPlayer::getPlayer($db->getField('account_id'), $game_id);
			$curr_ship =& $curr_player->getShip();
			
			$PHP_OUTPUT.=('<tr>');
			$PHP_OUTPUT.=('<td align="right">Game ID:</td><td>'.$game_id.'</td></tr><tr>');
			$PHP_OUTPUT.=('<td align="right">Name:</td>');
			$PHP_OUTPUT.=('<td><input type=text name=player_name['.$game_id.'] value="'.$curr_player->getPlayerName().'">('.$curr_player->getPlayerID().')</td>');
			$PHP_OUTPUT.=('</tr>');
			$PHP_OUTPUT.=('<tr>');
			$PHP_OUTPUT.=('<td align="right">Experience:</td>');
			$PHP_OUTPUT.=('<td>' . number_format($curr_player->getExperience()) . '</td>');
			$PHP_OUTPUT.=('</tr>');
			$PHP_OUTPUT.=('<tr>');
			$PHP_OUTPUT.=('<td align="right">Ship:</td>');
			$PHP_OUTPUT.=('<td>'.$curr_ship->getName().' (' . $curr_ship->getAttackRating() . '/' . $curr_ship->getDefenseRating() . ')</td></tr>');
			$PHP_OUTPUT.=('<tr><td><input type="radio" name="delete['.$game_id.']" value="TRUE" unchecked>Yes<input type="radio" name="delete['.$game_id.']" value="FALSE" checked>No</td><td>Delete player</td>');
			$PHP_OUTPUT.=('</tr>');

		}
		$PHP_OUTPUT.=('</table>');
		$PHP_OUTPUT.=('</td>');

	} else
		$PHP_OUTPUT.=('<td>Joined no active games</td>');

	$PHP_OUTPUT.=('</tr>');

	$PHP_OUTPUT.=('<tr><td>&nbsp;</td><td><hr noshade style="height:1px; border:1px solid white;"></td></tr>');

	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right" valign="top" class="bold">Donation:</td>');
	$PHP_OUTPUT.=('<td><input type="text" name="donation" size="5" id="InputFields" class="center">$</td>');
	$PHP_OUTPUT.=('</tr>');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td>&nbsp;</td>');
	$PHP_OUTPUT.=('<td><input type="checkbox" name="smr_credit" checked> Grant SMR Credits</td>');
	$PHP_OUTPUT.=('</tr>');
	
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right" valign="top" class="bold">Grant Reward SMR Credits:</td>');
	$PHP_OUTPUT.=('<td><input type="text" name="grant_credits" size="5" id="InputFields" class="center"> Credits</td>');
	$PHP_OUTPUT.=('</tr>');

	$PHP_OUTPUT.=('<tr><td>&nbsp;</td><td><hr noshade style="height:1px; border:1px solid white;"></td></tr>');
	
	$PHP_OUTPUT.='
	<SCRIPT LANGUAGE=JavaScript>
	function go()
	{
		var val = window.document.form_acc.reason_pre_select.value;
		
		if (val == 2) {
			alert("Please use the following syntax when you enter the multi closing info: \'Match list:1+5+7\' Thanks");
			window.document.form_acc.suspicion.value = \'Match list:\';
			window.document.form_acc.suspicion.disabled=false;
			window.document.form_acc.suspicion.focus();
		} else {
			window.document.form_acc.suspicion.value = \'For Multi Closings Only\';
			window.document.form_acc.suspicion.disabled=true;
		}
		window.document.form_acc.choise[0class="bold"ript>';
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right" valign="top" class="bold">Close Reason:</td>');
	$PHP_OUTPUT.=('<td>');
	$PHP_OUTPUT.=('<p>Reopen type:<input type="radio" name="reopen_type" value="account">Account close <input type="radio" name="reopen_type" value="mail">Mail ban</p>');
	$PHP_OUTPUT.=('<p><input type="radio" name="choise" value="pre_select">');
	$PHP_OUTPUT.=('<select name="reason_pre_select" onchange=go()>');
	$PHP_OUTPUT.=('<option value="0">[Please Select]</option>');

	$db->query('SELECT * FROM account_is_closed ' .
			   'WHERE account_id = '.$curr_account->account_id);
	if ($db->nextRecord())
		$curr_reason_id = $db->getField('reason_id');

	$db->query('SELECT * FROM closing_reason');
	while ($db->nextRecord()) {

		$reason_id	= $db->getField('reason_id');
		$reason		= $db->getField('reason');
		if (strlen($reason) > 50)
			$reason = substr($reason, 0, 75) . '...';

		$PHP_OUTPUT.=('<option value="'.$reason_id.'"');
		if ($curr_reason_id == $reason_id)
			$PHP_OUTPUT.=(' selected');
		$PHP_OUTPUT.=('>'.$reason.'</option>');

	}
	$PHP_OUTPUT.=('</select></p>');
	$PHP_OUTPUT.=('<p><input type="radio" name="choise" value="individual">');
	$PHP_OUTPUT.=('<input type="text" name="reason_msg" id="InputFields" style="width:400px;"></p>');
	$PHP_OUTPUT.=('<p><input type="radio" name="choise" value="reopen">Reopen!</p>');
	$PHP_OUTPUT.=('<p><input type=text name=suspicion id="InputFields" disabled=true style="width:400px;" value="For Multi Closings Only"></p>');
	$PHP_OUTPUT.=('<p>Mail ban: <input type="text" name="mailban" id="InputFields" class="center" style="width:30px;"> days</p>');
	$PHP_OUTPUT.=('<p>Points: <input type="text" name="points" id="InputFields" class="center" style="width:30px;"> points</p>');
	$db->query('SELECT * FROM account_is_closed WHERE account_id = '.$account_id);
	if ($db->nextRecord()) {
		$cont = 'yes';
		$expireTime = $db->getField('expires');
	}
	if ($expireTime > 0) $PHP_OUTPUT.=('<p>The account is set to reopen at ' . date(DATE_FULL_SHORT, $expireTime) . '.</p>');
	elseif (isset($cont)) $PHP_OUTPUT.=('<p>The account is closed indefinitely (oooo a big word).</p>');
	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('</tr>');

	$PHP_OUTPUT.=('<tr><td>&nbsp;</td><td><hr noshade style="height:1px; border:1px solid white;"></td></tr>');

	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right" valign="top" class="bold">Closing History:</td>');
	$PHP_OUTPUT.=('<td>');
	$db->query('SELECT * FROM account_has_closing_history WHERE account_id = '.$curr_account->account_id.' ORDER BY time ASC');
	if ($db->getNumRows())
	{

		while ($db->nextRecord())
		{

			$curr_time	= $db->getField('time');
			$action		= $db->getField('action');
			$admin_id	= $db->getField('admin_id');

			// if an admin did it we get his/her name
			if ($admin_id > 0) {

				$admin_account =& SmrAccount::getAccount($admin_id);

				$admin = $admin_account->login;

			} else
				$admin = 'System';

			$PHP_OUTPUT.=(date(DATE_FULL_SHORT, $curr_time) . ' - '.$action.' by '.$admin.'<br />');

		}

	} else
		$PHP_OUTPUT.=('no activity.');

	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('</tr>');

	$PHP_OUTPUT.=('<tr><td>&nbsp;</td><td><hr noshade style="height:1px; border:1px solid white;"></td></tr>');

	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right" valign="top" class="bold">Exception:</td>');
	$db->query('SELECT * FROM account_exceptions WHERE account_id = '.$curr_account->account_id);
	if ($db->nextRecord()) {

		$reason_txt = $db->getField('reason');
		$PHP_OUTPUT.=('<td>'.$reason_txt.'</td>');

	} else
		$PHP_OUTPUT.=('<td>This account is not listed.<br /><input type=text name=exception_add value="Add An Exception"></td>');
	$PHP_OUTPUT.=('</tr>');

	$PHP_OUTPUT.=('<tr><td>&nbsp;</td><td><hr noshade style="height:1px; border:1px solid white;"></td></tr>');

	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right" valign="top" class="bold">Forced Veteran:</td>');
	$PHP_OUTPUT.=('<td>');
	$PHP_OUTPUT.=('<input type="radio" name="veteran_status" value="TRUE"');
	if ($curr_account->isVeteranBumped())
		$PHP_OUTPUT.=(' checked');
	$PHP_OUTPUT.=('>Yes</td></tr><tr><td>&nbsp;</td><td>');
	$PHP_OUTPUT.=('<input type="radio" name="veteran_status" value="FALSE"');
	if (!$curr_account->isVeteranBumped())
		$PHP_OUTPUT.=(' checked');
	$PHP_OUTPUT.=('>No</td></tr>');

	$PHP_OUTPUT.=('<tr><td>&nbsp;</td><td><hr noshade style="height:1px; border:1px solid white;"></td></tr>');

	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right" valign="top" class="bold">Logging:</td>');
	$PHP_OUTPUT.=('<td>');
	$PHP_OUTPUT.=('<input type="radio" name="logging_status" value="TRUE"');
	if ($curr_account->isLoggingEnabled())
		$PHP_OUTPUT.=(' checked');
	$PHP_OUTPUT.=('>Yes</td></tr><tr><td>&nbsp;</td><td>');
	$PHP_OUTPUT.=('<input type="radio" name="logging_status" value="FALSE"');
	if (!$curr_account->isLoggingEnabled())
		$PHP_OUTPUT.=(' checked');
	$PHP_OUTPUT.=('>No</td></tr>');

	$PHP_OUTPUT.=('<tr><td>&nbsp;</td><td><hr noshade style="height:1px; border:1px solid white;"></td></tr>');

	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right" valign="top" class="bold">Last IP\'s:</td>');
	$PHP_OUTPUT.=('<td>');

	$PHP_OUTPUT.=('<table>');
	$db->query('SELECT ip, time, host FROM account_has_ip WHERE account_id = '.$curr_account->account_id.' ORDER BY time DESC');
	while ($db->nextRecord())
	{

		$curr_ip	= $db->getField('ip');
		$curr_time	= $db->getField('time');
		$host		= $db->getField('host');
		if ($host == $curr_ip)
			$host = 'unknown';
		$PHP_OUTPUT.=('<tr><td>' . date(DATE_FULL_SHORT, $curr_time) . '</td><td>&nbsp;</td><td>'.$curr_ip.'</td><td>&nbsp;</td><td>'.$host.'</td></tr>');

	}
	$PHP_OUTPUT.=('</table>');

	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('</tr>');

} else {

	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right" class="bold">Player Name:</td>');
	$PHP_OUTPUT.=('<td><input type="text" name="player_name" id="InputFields" size="20"></td>');
	$PHP_OUTPUT.=('</tr>');

}

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</p>');

$PHP_OUTPUT.=( '<table>' );
$PHP_OUTPUT.=( '<tr><td>' );
if ($curr_account && $curr_account->account_id != 0)
	$PHP_OUTPUT.=create_submit('Edit Account');
else
	$PHP_OUTPUT.=create_submit('Search');
$PHP_OUTPUT.=( '</td>' );
$PHP_OUTPUT.=( '</form>' );

$container = array();

if ($curr_account && $curr_account->account_id != 0) {

	$PHP_OUTPUT.=create_echo_form(create_container('skeleton.php', 'account_edit.php'));
	$PHP_OUTPUT.=('<td>');
	$PHP_OUTPUT.=create_submit('Reset Form');
	$PHP_OUTPUT.=('</td>' );
	$PHP_OUTPUT.=('</form>' );

}

$PHP_OUTPUT.=( '</table>' );

if(isset($var['errorMsg']))
	$PHP_OUTPUT.=('<div align="center">'.$var['errorMsg'].'</div>');
$PHP_OUTPUT.=('<div align="center">'.$var['msg'].'</div>');

?>
