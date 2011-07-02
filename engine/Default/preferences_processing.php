<?php

$container = array();
$container['url'] = 'skeleton.php';
if (SmrSession::$game_id > 0)
	if ($player->isLandedOnPlanet()) $container['body'] = 'planet_main.php'; else $container['body'] = 'current_sector.php';
else
	$container['body'] = 'game_play.php';
$action = $_REQUEST['action'];
$email = $_REQUEST['email'];
$new_password = $_REQUEST['new_password'];
$old_password = $_REQUEST['old_password'];
$retype_password = $_REQUEST['retype_password'];
$HoF_name = trim($_REQUEST['HoF_name']);
$ircNick = trim($_REQUEST['irc_nick']);

if (USE_COMPATIBILITY && $action == 'Link Account')
{
	if(!$account->linkAccount($_REQUEST['oldAccountLogin'],$_REQUEST['oldAccountPassword']))
		create_error('There is no old account with that username/password.');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have linked your old account.';
}
else if ($action == 'Save and resend validation code')
{
	// get user and host for the provided address
	list($user, $host) = explode('@', $email);

	// check if the host got a MX or at least an A entry
	if (!checkdnsrr($host, 'MX') && !checkdnsrr($host, 'A'))
		create_error('This is not a valid email address! The domain '.$db->escapeString($host).' does not exist.');

	if (strstr($email, ' '))
		create_error('The eMail is invalid! It cannot contain any spaces.');
	
	$db->query('SELECT * FROM account WHERE email = '.$db->escapeString($email).' and account_id != ' . $account->account_id);
	if ($db->getNumRows() > 0)
		create_error('This eMail address is already registered.');

	$account->email = $email;
	$account->validation_code = substr(SmrSession::$session_id, 0, 10);
	$account->isValidated(false);
	$account->update();

	// remember when we sent validation code
	$db->query('REPLACE INTO notification (notification_type, account_id, time) ' .
									 'VALUES(\'validation_code\', '.SmrSession::$account_id.', ' . TIME . ')');

	mail($email, 'Your validation code!',
		'You changed your email address registered within SMR and need to revalidate now!'.EOL.EOL.
		'   Your new validation code is: '.$account->validation_code.EOL.EOL.
		'The Space Merchant Realms server is on the web at '.URL.'/.'.EOL.
		'You\'ll find a quick how-to-play here '.URL.'/manual.php'.EOL.
		'Please verify within the next 7 days or your account will be automatically deleted.',
		'From: support@smrealms.de');

	// get rid of that email permission
	$db->query('DELETE FROM account_is_closed ' .
						'WHERE account_id = '.$account->account_id.' AND ' .
								'reason_id = 1');

	// overwrite container
	$container['body'] = 'validate.php';
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your email address, you will now need to revalidate with the code sent to the new email address.';
}
elseif ($action == 'Change Password')
{
	if (empty($new_password))
		create_error('You must enter a non empty password!');

	if ($account->checkPassword($old_password))
		create_error('Your current password is wrong!');

	if ($new_password != $retype_password)
		create_error('The passwords you entered don\'t match!');

	if ($new_password == $account->login)
		create_error('Your chosen password is invalid!');

	$account->setPassword($new_password);
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your password.';
}
elseif ($action == 'Change Name')
{
	$limited_char = 0;
	for ($i = 0; $i < strlen($HoF_name); $i++) {
		// disallow certain ascii chars
		if (ord($HoF_name[$i]) < 32 || ord($HoF_name[$i]) > 127)
			create_error('Your Hall Of Fame name contains invalid characters!');

		// numbers 48..57
		// Letters 65..90
		// letters 97..122
		if (!((ord($HoF_name[$i]) >= 48 && ord($HoF_name[$i]) <= 57) ||
			  (ord($HoF_name[$i]) >= 65 && ord($HoF_name[$i]) <= 90) ||
			  (ord($HoF_name[$i]) >= 97 && ord($HoF_name[$i]) <= 122)))
		{
			$limited_char += 1;
		}
	}

	if ($limited_char > 4)
		create_error('You cannot use a name with more than 4 special characters.');


	//disallow blank names
	if (empty($HoF_name) || $HoF_name == '') create_error('You Hall of Fame name must contain characters!');

	//no duplicates
	$db->query('SELECT * FROM account WHERE hof_name = ' . $db->escape_string($HoF_name, true) . ' AND account_id != '.$account->getAccountID().' LIMIT 1');
	if ($db->nextRecord()) create_error('Someone is already using that name!');

	// set the HoF name in account stat
	$account->setHofName($HoF_name);
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your hall of fame name.';
}
elseif ($action == 'Change Nick')
{
	for ($i = 0; $i < strlen($ircNick); $i++) {
		// disallow certain ascii chars (and whitespace!)
		if (ord($ircNick[$i]) < 33 || ord($ircNick[$i]) > 127)
			create_error('Your IRC Nick contains invalid characters!');
	}

	// here you can delete your registered irc nick
	if (empty($ircNick) || $ircNick == '') {
		$account->setIrcNick(null);
		$container['msg'] = '<span class="green">SUCCESS: </span>You have deleted your irc nick.';
	} else {

		// no duplicates
		$db->query('SELECT * FROM account WHERE irc_nick = ' . $db->escape_string($ircNick, true) . ' AND account_id != '.$account->getAccountID().' LIMIT 1');
		if ($db->nextRecord()) create_error('Someone is already using that nick!');

		// save irc nick in db and set message
		$account->setIrcNick($ircNick);
		$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your irc nick.';

	}

}
elseif ($action == 'Yes')
{
	$account_id = $var['account_id'];
	$amount = $var['amount'];

	// create his account
	$his_account =& SmrAccount::getAccount($account_id);
	
	// take from us
	$account->decreaseSmrCredits($amount);
	// add to him
	$his_account->increaseSmrCredits($amount);
	$container['msg'] = '<span class="green">SUCCESS: </span>You have sent SMR credits.';
}
elseif ($action == 'Change Timezone')
{
	$timez = $_REQUEST['timez'];
	if (!is_numeric($timez))
		create_error('Numbers only please');

	$db->query('UPDATE account SET offset = '.$timez.' WHERE account_id = '.SmrSession::$account_id);
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your time offset.';
}
elseif ($action == 'Change Date Formats')
{
	$account->setShortDateFormat($_REQUEST['dateformat']);
	$account->setShortTimeFormat($_REQUEST['timeformat']);
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your date formats.';
}
elseif ($action == 'Change Images')
{
	$account->images = $_REQUEST['images'];
	$account->update();
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your ship images preferences.';
}
elseif ($action == 'Change Centering')
{
	$account->setCenterGalaxyMapOnPlayer($_REQUEST['centergalmap']=='Yes');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your centering galaxy map preferences.';
}
else if ($action == 'Change Size' && is_numeric($_REQUEST['fontsize']) && $_REQUEST['fontsize'] >= 50)
{
	$account->setFontSize($_REQUEST['fontsize']);
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your font size.';
}
else if ($action == 'Change CSS Options')
{
	$account->setCssLink($_REQUEST['csslink']);
	$account->setDefaultCSSEnabled($_REQUEST['defaultcss']!='No');
	if(isset($_REQUEST['template']))
		$account->setTemplate($_REQUEST['template']);
	if(isset($_REQUEST['colour_scheme'])&&in_array($_REQUEST['colour_scheme'],Globals::getAvailableColourSchemes($account->getTemplate())))
		$account->setColourScheme($_REQUEST['colour_scheme']);
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your CSS options.';
}
else if ($action == 'Change Kamikaze Setting')
{
	$player->setCombatDronesKamikazeOnMines($_REQUEST['kamikaze']=='Yes');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your combat drones options.';
}
else if ($action == 'Change Message Setting')
{
	$player->setForceDropMessages($_REQUEST['forceDropMessages']=='Yes');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your message options.';
}
else if (strpos(trim($action),'Alter Player')===0)
{
	// trim input now
	$player_name = trim($_POST['PlayerName']);
	
	$old_name = $player->getName();
	
	if($old_name == $player_name)
	{
		create_error('Your player already has that name!');
	}
	
	$limited_char = 0;
	for ($i = 0; $i < strlen($player_name); $i++) {
		// disallow certain ascii chars
		if (ord($player_name[$i]) < 32 || ord($player_name[$i]) > 127)
			create_error('The player name contains invalid characters!');

		// numbers 48..57
		// Letters 65..90
		// letters 97..122
		if (!((ord($player_name[$i]) >= 48 && ord($player_name[$i]) <= 57) ||
			  (ord($player_name[$i]) >= 65 && ord($player_name[$i]) <= 90) ||
			  (ord($player_name[$i]) >= 97 && ord($player_name[$i]) <= 122)))
		{
			$limited_char += 1;
		}
	}

	if ($limited_char > 4)
		create_error('You cannot use a name with more than 4 special characters.');

	if (empty($player_name))
		create_error('You must enter a player name!');
	
	// Check if name is in use.
	$db->query('SELECT account_id FROM player WHERE game_id=' .SmrSession::$game_id . ' AND player_name=' . $db->escape_string($player_name) . ' LIMIT 1' );
	if($db->getNumRows())
	{
		create_error('Name is already being used in this game!');
	}
	
	if($player->isNameChanged())
	{
		if($account->getTotalSmrCredits()<CREDITS_PER_NAME_CHANGE)
		{
			create_error('You do not have enough credits to change your name.');
		}
		$account->decreaseTotalSmrCredits(CREDITS_PER_NAME_CHANGE);
	}
	
	$player->setPlayerName($player_name);

	$news = '<span class="blue">ADMIN</span> Please be advised that ' . $old_name . ' has changed their name to ' . $player->getBBLink() . '</span>';
	$db->query('INSERT INTO news (time, news_message, game_id, dead_id,dead_alliance) VALUES (' . TIME . ',' . $db->escape_string($news, FALSE) . ',' . SmrSession::$game_id . ','.$player->getAccountID().','.$player->getAllianceID().')');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your player name.';
}

forward($container);

?>