<?php declare(strict_types=1);

$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$account = $session->getAccount();

$container = Page::create('skeleton.php');
if ($session->hasGame()) {
	$container['body'] = 'current_sector.php';
	$player = $session->getPlayer();
} else {
	$container['body'] = 'game_play.php';
}
$action = Smr\Request::get('action');

if ($action == 'Save and resend validation code') {
	$email = Smr\Request::get('email');

	$account->changeEmail($email);

	// overwrite container
	$container['body'] = 'validate.php';
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your email address, you will now need to revalidate with the code sent to the new email address.';
} elseif ($action == 'Change Password') {
	$new_password = Smr\Request::get('new_password');
	$old_password = Smr\Request::get('old_password');
	$retype_password = Smr\Request::get('retype_password');

	if (empty($new_password)) {
		create_error('You must enter a non empty password!');
	}

	if (!$account->checkPassword($old_password)) {
		create_error('Your current password is wrong!');
	}

	if ($new_password != $retype_password) {
		create_error('The passwords you entered don\'t match!');
	}

	if ($new_password == $account->getLogin()) {
		create_error('Your chosen password is invalid!');
	}

	$account->setPassword($new_password);
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your password.';
} elseif ($action == 'Change Name') {
	$HoF_name = trim(Smr\Request::get('HoF_name'));

	Smr\DisplayNameValidator::validate($HoF_name);

	//no duplicates
	$dbResult = $db->read('SELECT 1 FROM account WHERE hof_name = ' . $db->escapeString($HoF_name) . ' AND account_id != ' . $db->escapeNumber($account->getAccountID()) . ' LIMIT 1');
	if ($dbResult->hasRecord()) {
		create_error('Someone is already using that name!');
	}

	// set the HoF name in account stat
	$account->setHofName($HoF_name);
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your hall of fame name.';
} elseif ($action == 'Change Discord ID') {
	$discordId = trim(Smr\Request::get('discord_id'));

	if (empty($discordId)) {
		$account->setDiscordId(null);
		$container['msg'] = '<span class="green">SUCCESS: </span>You have deleted your Discord User ID.';

	} else {
		// no duplicates
		$dbResult = $db->read('SELECT 1 FROM account WHERE discord_id =' . $db->escapeString($discordId) . ' AND account_id != ' . $db->escapeNumber($account->getAccountID()) . ' LIMIT 1');
		if ($dbResult->hasRecord()) {
			create_error('Someone is already using that Discord User ID!');
		}

		$account->setDiscordId($discordId);
		$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your Discord User ID.';
	}
} elseif ($action == 'Change IRC Nick') {
	$ircNick = trim(Smr\Request::get('irc_nick'));

	// here you can delete your registered irc nick
	if (empty($ircNick)) {
		$account->setIrcNick(null);
		$container['msg'] = '<span class="green">SUCCESS: </span>You have deleted your irc nick.';
	} else {
		// Disallow control characters and spaces
		if (!ctype_graph($ircNick)) {
			create_error('Your IRC Nick may only contain visible printed characters!');
		}

		// no duplicates
		$dbResult = $db->read('SELECT 1 FROM account WHERE irc_nick = ' . $db->escapeString($ircNick) . ' AND account_id != ' . $db->escapeNumber($account->getAccountID()) . ' LIMIT 1');
		if ($dbResult->hasRecord()) {
			create_error('Someone is already using that nick!');
		}

		// save irc nick in db and set message
		$account->setIrcNick($ircNick);
		$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your irc nick.';

	}

} elseif ($action == 'Yes') {
	$var = $session->getCurrentVar();
	$account_id = $var['account_id'];
	$amount = $var['amount'];

	// create his account
	$his_account = SmrAccount::getAccount($account_id);

	// take from us
	$account->decreaseSmrCredits($amount);
	// add to him
	$his_account->increaseSmrCredits($amount);
	$container['msg'] = '<span class="green">SUCCESS: </span>You have sent SMR credits.';
} elseif ($action == 'Change Timezone') {
	$timez = Smr\Request::getInt('timez');

	$db->write('UPDATE account SET offset = ' . $db->escapeNumber($timez) . ' WHERE account_id = ' . $db->escapeNumber($account->getAccountID()));
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your time offset.';
} elseif ($action == 'Change Date Formats') {
	$account->setDateFormat(Smr\Request::get('dateformat'));
	$account->setTimeFormat(Smr\Request::get('timeformat'));
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your date formats.';
} elseif ($action == 'Change Images') {
	$account->setDisplayShipImages(Smr\Request::get('images') == 'Yes');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your ship images preferences.';
} elseif ($action == 'Change Centering') {
	$account->setCenterGalaxyMapOnPlayer(Smr\Request::get('centergalmap') == 'Yes');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your centering galaxy map preferences.';
} elseif ($action == 'Change Size') {
	$fontsize = Smr\Request::getInt('fontsize');
	if ($fontsize < 50) {
		create_error('Minimum font size is 50%');
	}
	$account->setFontSize($fontsize);
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your font size.';
} elseif ($action == 'Change CSS Options') {
	$account->setCssLink(Smr\Request::get('csslink'));
	$cssTemplateAndColor = Smr\Request::get('template');
	if ($cssTemplateAndColor == 'None') {
		$account->setDefaultCSSEnabled(false);
	} else {
		$account->setDefaultCSSEnabled(true);
		list($cssTemplate, $cssColourScheme) = explode(' - ', $cssTemplateAndColor);
		$account->setTemplate($cssTemplate);
		$account->setColourScheme($cssColourScheme);
	}
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your CSS options.';
} elseif ($action == 'Change Kamikaze Setting') {
	$player->setCombatDronesKamikazeOnMines(Smr\Request::get('kamikaze') == 'Yes');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your combat drones options.';
} elseif ($action == 'Change Message Setting') {
	$player->setForceDropMessages(Smr\Request::get('forceDropMessages') == 'Yes');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your message options.';
} elseif ($action == 'Save Hotkeys') {
	foreach (AbstractSmrAccount::getDefaultHotkeys() as $hotkey => $binding) {
		$account->setHotkey($hotkey, explode(' ', Smr\Request::get($hotkey)));
	}
	$container['msg'] = '<span class="green">SUCCESS: </span>You have saved your hotkeys.';
} elseif ($action == 'change_name') {
	// trim input now
	$player_name = trim(Smr\Request::get('PlayerName'));

	if ($player->getPlayerName() == $player_name) {
		create_error('Your player already has that name!');
	}

	Smr\DisplayNameValidator::validate($player_name);

	// Check if name is in use.
	// The player_name field has case-insensitive collation, so check against ID
	// to allow player to change the case of their name.
	$dbResult = $db->read('SELECT 1 FROM player WHERE game_id=' . $db->escapeNumber($player->getGameID()) . ' AND player_name=' . $db->escapeString($player_name) . ' AND player_id != ' . $db->escapeNumber($player->getPlayerID()) . ' LIMIT 1');
	if ($dbResult->hasRecord()) {
		create_error('Name is already being used in this game!');
	}

	if ($player->isNameChanged()) {
		if ($account->getTotalSmrCredits() < CREDITS_PER_NAME_CHANGE) {
			create_error('You do not have enough credits to change your name.');
		}
		$account->decreaseTotalSmrCredits(CREDITS_PER_NAME_CHANGE);
	}

	$old_name = $player->getDisplayName();

	$player->setPlayerNameByPlayer($player_name);

	$news = 'Please be advised that ' . $old_name . ' has changed their name to ' . $player->getBBLink();
	$db->write('INSERT INTO news (time, news_message, game_id, type, killer_id) VALUES (' . $db->escapeNumber(Smr\Epoch::time()) . ',' . $db->escapeString($news) . ',' . $db->escapeNumber($player->getGameID()) . ', \'admin\', ' . $db->escapeNumber($player->getAccountID()) . ')');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your player name.';
} elseif ($action == 'change_race') {
	if (!$player->canChangeRace()) {
		throw new Exception('Player is not allowed to change their race!');
	}
	$newRaceID = Smr\Request::getInt('race_id');
	if (!in_array($newRaceID, $player->getGame()->getPlayableRaceIDs())) {
		throw new Exception('Invalid race ID selected!');
	}
	if ($newRaceID == $player->getRaceID()) {
		create_error('You are already the ' . $player->getRaceName() . ' race!');
	}

	// Modify the player
	$oldRaceID = $player->getRaceID();
	$player->setRaceID($newRaceID);
	$player->setSectorID($player->getHome());
	$player->setLandedOnPlanet(false);
	$player->getSector()->markVisited($player);
	$player->getShip()->getPod($player->hasNewbieStatus()); // just to reset
	$player->getShip()->giveStarterShip();
	$player->setNewbieTurns(max(1, $player->getNewbieTurns()));
	$player->setExperience(0);
	$player->setRaceChanged(true);

	// Reset relations
	$db->write('DELETE FROM player_has_relation WHERE ' . $player->getSQL());
	$player->giveStartingRelations();

	$news = 'Please be advised that ' . $player->getBBLink() . ' has changed their race from [race=' . $oldRaceID . '] to [race=' . $player->getRaceID() . ']';
	$db->write('INSERT INTO news (time, news_message, game_id, type, killer_id) VALUES (' . $db->escapeNumber(Smr\Epoch::time()) . ',' . $db->escapeString($news) . ',' . $db->escapeNumber($player->getGameID()) . ', \'admin\', ' . $db->escapeNumber($player->getAccountID()) . ')');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your player race.';
} elseif ($action == 'Update Colours') {
	$friendlyColour = Smr\Request::get('friendly_color');
	$neutralColour = Smr\Request::get('neutral_color');
	$enemyColour = Smr\Request::get('enemy_color');

	if (strlen($friendlyColour) == 6) {
		$account->setFriendlyColour($friendlyColour);
	}
	if (strlen($neutralColour) == 6) {
		$account->setNeutralColour($neutralColour);
	}
	if (strlen($enemyColour) == 6) {
		$account->setEnemyColour($enemyColour);
	}
}

// Update the account in case it has changed
$account->update();

$container->go();
