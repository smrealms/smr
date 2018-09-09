<?php

$container = create_container('skeleton.php');
if (SmrSession::$game_id > 0) {
	$container['body'] = 'current_sector.php';
}
else {
	$container['body'] = 'game_play.php';
}
$action = $_REQUEST['action'];

if (Globals::useCompatibilityDatabases() && $action == 'Link Account') {
	if(!$account->linkAccount($_REQUEST['oldAccountLogin'],$_REQUEST['oldAccountPassword'])) {
		create_error('There is no old account with that username/password.');
	}
	$container['msg'] = '<span class="green">SUCCESS: </span>You have linked your old account.';
}
else if ($action == 'Save and resend validation code') {
	$email = $_REQUEST['email'];

	$account->changeEmail($_REQUEST['email']);

	// overwrite container
	$container['body'] = 'validate.php';
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your email address, you will now need to revalidate with the code sent to the new email address.';
}
elseif ($action == 'Change Password') {
	$new_password = $_REQUEST['new_password'];
	$old_password = $_REQUEST['old_password'];
	$retype_password = $_REQUEST['retype_password'];

	if (empty($new_password))
		create_error('You must enter a non empty password!');

	if (!$account->checkPassword($old_password))
		create_error('Your current password is wrong!');

	if ($new_password != $retype_password)
		create_error('The passwords you entered don\'t match!');

	if ($new_password == $account->getLogin())
		create_error('Your chosen password is invalid!');

	$account->setPassword($new_password);
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your password.';
}
elseif ($action == 'Change Name') {
	$HoF_name = trim($_REQUEST['HoF_name']);

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
			(ord($HoF_name[$i]) >= 97 && ord($HoF_name[$i]) <= 122))) {
			$limited_char += 1;
		}
	}

	if ($limited_char > 4)
		create_error('You cannot use a name with more than 4 special characters.');


	//disallow blank names
	if (empty($HoF_name) || $HoF_name == '') create_error('You Hall of Fame name must contain characters!');

	//no duplicates
	$db->query('SELECT * FROM account WHERE hof_name = ' . $db->escape_string($HoF_name, true) . ' AND account_id != '.$db->escapeNumber($account->getAccountID()).' LIMIT 1');
	if ($db->nextRecord()) create_error('Someone is already using that name!');

	// set the HoF name in account stat
	$account->setHofName($HoF_name);
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your hall of fame name.';
}

elseif ($action == 'Change Discord ID') {
	$discordId = trim($_REQUEST['discord_id']);

	if (empty($discordId)) {
		$account->setDiscordId(null);
		$container['msg'] = '<span class="green">SUCCESS: </span>You have deleted your Discord User ID.';

	} else {
		// no duplicates
		$db->query('SELECT * FROM account WHERE discord_id =' . $db->escapeString($discordId) . ' AND account_id != '.$db->escapeNumber($account->getAccountID()).' LIMIT 1');
		if ($db->nextRecord()) create_error('Someone is already using that Discord User ID!');

		$account->setDiscordId($discordId);
		$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your Discord User ID.';
	}
}

elseif ($action == 'Change IRC Nick') {
	$ircNick = trim($_REQUEST['irc_nick']);

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
		$db->query('SELECT * FROM account WHERE irc_nick = ' . $db->escape_string($ircNick, true) . ' AND account_id != '.$db->escapeNumber($account->getAccountID()).' LIMIT 1');
		if ($db->nextRecord()) create_error('Someone is already using that nick!');

		// save irc nick in db and set message
		$account->setIrcNick($ircNick);
		$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your irc nick.';

	}

}
elseif ($action == 'Yes') {
	$account_id = $var['account_id'];
	$amount = $var['amount'];

	// create his account
	$his_account = SmrAccount::getAccount($account_id);

	// take from us
	$account->decreaseSmrCredits($amount);
	// add to him
	$his_account->increaseSmrCredits($amount);
	$container['msg'] = '<span class="green">SUCCESS: </span>You have sent SMR credits.';
}
elseif ($action == 'Change Timezone') {
	$timez = $_REQUEST['timez'];
	if (!is_numeric($timez))
		create_error('Numbers only please');

	$db->query('UPDATE account SET offset = '.$db->escapeNumber($timez).' WHERE account_id = '.$db->escapeNumber($account->getAccountID()));
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your time offset.';
}
elseif ($action == 'Change Date Formats') {
	$account->setShortDateFormat($_REQUEST['dateformat']);
	$account->setShortTimeFormat($_REQUEST['timeformat']);
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your date formats.';
}
elseif ($action == 'Change Images') {
	$account->setDisplayShipImages($_REQUEST['images']);
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your ship images preferences.';
}
elseif ($action == 'Change Centering') {
	$account->setCenterGalaxyMapOnPlayer($_REQUEST['centergalmap']=='Yes');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your centering galaxy map preferences.';
}
else if ($action == 'Change Size' && is_numeric($_REQUEST['fontsize']) && $_REQUEST['fontsize'] >= 50) {
	$account->setFontSize($_REQUEST['fontsize']);
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your font size.';
}
else if ($action == 'Change CSS Options') {
	$account->setCssLink($_REQUEST['csslink']);
	if ($_REQUEST['template'] == 'None') {
		$account->setDefaultCSSEnabled(false);
	} else {
		$account->setDefaultCSSEnabled(true);
		list($cssTemplate, $cssColourScheme) = explode(' - ', $_REQUEST['template']);
		$account->setTemplate($cssTemplate);
		$account->setColourScheme($cssColourScheme);
	}
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your CSS options.';
}
else if ($action == 'Change Kamikaze Setting') {
	$player->setCombatDronesKamikazeOnMines($_REQUEST['kamikaze']=='Yes');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your combat drones options.';
}
else if ($action == 'Change Message Setting') {
	$player->setForceDropMessages($_REQUEST['forceDropMessages']=='Yes');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your message options.';
}
else if ($action == 'Save Hotkeys') {
	foreach(AbstractSmrAccount::getDefaultHotkeys() as $hotkey => $binding) {
		if(isset($_REQUEST[$hotkey])) {
			$account->setHotkey($hotkey, explode(' ', $_REQUEST[$hotkey]));
		}
	}
	$container['msg'] = '<span class="green">SUCCESS: </span>You have saved your hotkeys.';
}
else if (strpos(trim($action),'Alter Player')===0) {
	// trim input now
	$player_name = trim($_POST['PlayerName']);

	$old_name = $player->getPlayerName();

	if($old_name == $player_name) {
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
			(ord($player_name[$i]) >= 97 && ord($player_name[$i]) <= 122))) {
			$limited_char += 1;
		}
	}

	if ($limited_char > 4)
		create_error('You cannot use a name with more than 4 special characters.');

	if (empty($player_name))
		create_error('You must enter a player name!');

	// Check if name is in use.
	// The player_name field has case-insensitive collation, so check against ID
	// to allow player to change the case of their name.
	$db->query('SELECT 1 FROM player WHERE game_id=' . $db->escapeNumber($player->getGameID()) . ' AND player_name=' . $db->escapeString($player_name) . ' AND player_id != ' . $db->escapeString($player->getPlayerID()) . ' LIMIT 1' );
	if ($db->getNumRows()) {
		create_error('Name is already being used in this game!');
	}

	if($player->isNameChanged()) {
		if($account->getTotalSmrCredits()<CREDITS_PER_NAME_CHANGE) {
			create_error('You do not have enough credits to change your name.');
		}
		$account->decreaseTotalSmrCredits(CREDITS_PER_NAME_CHANGE);
	}

	$player->setPlayerName($player_name);

	$news = '<span class="blue">ADMIN</span> Please be advised that ' . $old_name . ' has changed their name to ' . $player->getBBLink() . '</span>';
	$db->query('INSERT INTO news (time, news_message, game_id, dead_id,dead_alliance) VALUES (' . $db->escapeNumber(TIME) . ',' . $db->escape_string($news, FALSE) . ',' . $db->escapeNumber($player->getGameID()) . ',' . $db->escapeNumber($player->getAccountID()) . ',' . $db->escapeNumber($player->getAllianceID()) . ')');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your player name.';
}
else if ($action == 'Update Colours') {
	$friendlyColour = $_REQUEST['friendly_color'];
	$neutralColour = $_REQUEST['neutral_color'];
	$enemyColour = $_REQUEST['enemy_color'];

	if (strlen($friendlyColour) == 6) {
		$account->setFriendlyColour($friendlyColour);
	}
	if (strlen($neutralColour) == 6) {
		$account->setNeutralColour($neutralColour);
	}
	if (strlen($enemyColour) == 6) {
		$account->setEnemyColour($enemyColour);
	}
	$account->update();
}

forward($container);
