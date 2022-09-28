<?php declare(strict_types=1);

use Smr\Database;
use Smr\DisplayNameValidator;
use Smr\Exceptions\AccountNotFound;
use Smr\Request;

$db = Database::getInstance();
$session = Smr\Session::getInstance();
$account = $session->getAccount();

if ($session->hasGame()) {
	$container = Page::create('current_sector.php');
} else {
	$container = Page::create('game_play.php');
}
$action = Request::get('action');

if ($action == 'Save and resend validation code') {
	$email = Request::get('email');

	$account->changeEmail($email);

	// overwrite container
	$container = Page::create('validate.php');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your email address, you will now need to revalidate with the code sent to the new email address.';

} elseif ($action == 'Change Password') {
	$new_password = Request::get('new_password');
	$old_password = Request::get('old_password');
	$retype_password = Request::get('retype_password');

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
	$HoF_name = Request::get('HoF_name');

	DisplayNameValidator::validate($HoF_name);

	//no duplicates
	try {
		$other = SmrAccount::getAccountByHofName($HoF_name);
		if (!$account->equals($other)) {
			create_error('Someone is already using that Hall of Fame name!');
		}
	} catch (AccountNotFound) {
		// Proceed, this Hall of Fame name is not in use
	}

	// set the HoF name in account stat
	$account->setHofName($HoF_name);
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your Hall of Fame name.';

} elseif ($action == 'Change Discord ID') {
	$discordId = Request::get('discord_id');

	if (empty($discordId)) {
		$account->setDiscordId(null);
		$container['msg'] = '<span class="green">SUCCESS: </span>You have deleted your Discord User ID.';
	} else {
		// no duplicates
		try {
			$other = SmrAccount::getAccountByDiscordId($discordId);
			if (!$account->equals($other)) {
				create_error('Someone is already using that Discord User ID!');
			}
		} catch (AccountNotFound) {
			// Proceed, this Discord ID is not in use
		}

		$account->setDiscordId($discordId);
		$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your Discord User ID.';
	}

} elseif ($action == 'Change IRC Nick') {
	$ircNick = Request::get('irc_nick');

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
		try {
			$other = SmrAccount::getAccountByIrcNick($ircNick);
			if (!$account->equals($other)) {
				create_error('Someone is already using that IRC nick!');
			}
		} catch (AccountNotFound) {
			// Proceed, this IRC nick is not in use
		}

		// save irc nick in db and set message
		$account->setIrcNick($ircNick);
		$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your irc nick.';
	}

} elseif ($action == 'Yes') {
	// Confirmed SMR Credit transfer
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
	$timez = Request::getInt('timez');

	$db->write('UPDATE account SET offset = ' . $db->escapeNumber($timez) . ' WHERE account_id = ' . $db->escapeNumber($account->getAccountID()));
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your time offset.';

} elseif ($action == 'Change Date Formats') {
	$account->setDateFormat(Request::get('dateformat'));
	$account->setTimeFormat(Request::get('timeformat'));
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your date formats.';

} elseif ($action == 'Change Images') {
	$account->setDisplayShipImages(Request::get('images') == 'Yes');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your ship images preferences.';

} elseif ($action == 'Change Centering') {
	$account->setCenterGalaxyMapOnPlayer(Request::get('centergalmap') == 'Yes');
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your centering galaxy map preferences.';

} elseif ($action == 'Change Size') {
	$fontsize = Request::getInt('fontsize');
	if ($fontsize < 50) {
		create_error('Minimum font size is 50%');
	}
	$account->setFontSize($fontsize);
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your font size.';

} elseif ($action == 'Change CSS Options') {
	$account->setCssLink(Request::get('csslink'));
	$cssTemplateAndColor = Request::get('template');
	if ($cssTemplateAndColor == 'None') {
		$account->setDefaultCSSEnabled(false);
	} else {
		$account->setDefaultCSSEnabled(true);
		[$cssTemplate, $cssColourScheme] = explode(' - ', $cssTemplateAndColor);
		$account->setTemplate($cssTemplate);
		$account->setColourScheme($cssColourScheme);
	}
	$container['msg'] = '<span class="green">SUCCESS: </span>You have changed your CSS options.';

} elseif ($action == 'Save Hotkeys') {
	foreach (SmrAccount::getDefaultHotkeys() as $hotkey => $binding) {
		$account->setHotkey($hotkey, explode(' ', Request::get($hotkey)));
	}
	$container['msg'] = '<span class="green">SUCCESS: </span>You have saved your hotkeys.';

} elseif ($action == 'Update Colours') {
	$friendlyColour = Request::get('friendly_color');
	$neutralColour = Request::get('neutral_color');
	$enemyColour = Request::get('enemy_color');

	if (strlen($friendlyColour) == 6) {
		$account->setFriendlyColour($friendlyColour);
	}
	if (strlen($neutralColour) == 6) {
		$account->setNeutralColour($neutralColour);
	}
	if (strlen($enemyColour) == 6) {
		$account->setEnemyColour($enemyColour);
	}
} elseif ($action == 'Toggle Ajax') {
	$account->setUseAJAX(!$account->isUseAJAX());
	$status = $account->isUseAJAX() ? 'enabled' : 'disabled';
	$container['msg'] = '<span class="green">SUCCESS: </span> You have ' . $status . ' AJAX auto-refresh.';
} else {
	throw new Exception('Unknown action: ' . $action);
}

// Update the account in case it has changed
$account->update();

$container->go();
