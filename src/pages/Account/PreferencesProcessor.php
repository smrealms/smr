<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Exception;
use Smr\Account;
use Smr\Database;
use Smr\DisplayNameValidator;
use Smr\Exceptions\AccountNotFound;
use Smr\Html\Submit;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class PreferencesProcessor extends AccountPageProcessor {

	private const string ACTION = 'action';

	public readonly Submit $actionEmail;
	public readonly Submit $actionPassword;
	public readonly Submit $actionColors;
	public readonly Submit $actionHofName;
	public readonly Submit $actionDiscordId;
	public readonly Submit $actionIrcNick;
	public readonly Submit $actionTimezone;
	public readonly Submit $actionDateFormat;
	public readonly Submit $actionAjax;
	public readonly Submit $actionShipImages;
	public readonly Submit $actionGalMapCenter;
	public readonly Submit $actionFontSize;
	public readonly Submit $actionCss;
	public readonly Submit $actionHotkeys;

	public function __construct() {
		$this->actionEmail = new Submit(self::ACTION, 'email');
		$this->actionPassword = new Submit(self::ACTION, 'password');
		$this->actionColors = new Submit(self::ACTION, 'colors');
		$this->actionHofName = new Submit(self::ACTION, 'hof_name');
		$this->actionDiscordId = new Submit(self::ACTION, 'discord_id');
		$this->actionIrcNick = new Submit(self::ACTION, 'irc_nick');
		$this->actionTimezone = new Submit(self::ACTION, 'timezone');
		$this->actionDateFormat = new Submit(self::ACTION, 'date_format');
		$this->actionAjax = new Submit(self::ACTION, 'ajax');
		$this->actionShipImages = new Submit(self::ACTION, 'ship_images');
		$this->actionGalMapCenter = new Submit(self::ACTION, 'gal_map_center');
		$this->actionFontSize = new Submit(self::ACTION, 'font_size');
		$this->actionCss = new Submit(self::ACTION, 'css');
		$this->actionHotkeys = new Submit(self::ACTION, 'hotkeys');
	}

	public function build(Account $account): never {
		$action = Request::get(self::ACTION);

		if ($action === $this->actionEmail->value) {
			$email = Request::get('email');

			$account->changeEmail($email);
			$account->update();

			$message = '<span class="green">SUCCESS: </span>You have changed your email address, you will now need to revalidate with the code sent to the new email address.';
			(new Validate($message))->go();
		}

		if ($action === $this->actionPassword->value) {
			$new_password = Request::get('new_password');
			$old_password = Request::get('old_password');
			$retype_password = Request::get('retype_password');

			if ($new_password === '') {
				create_error('You must enter a non empty password!');
			}

			if (!$account->checkPassword($old_password)) {
				create_error('Your current password is wrong!');
			}

			if ($new_password !== $retype_password) {
				create_error('The passwords you entered don\'t match!');
			}

			if ($new_password === $account->getLogin()) {
				create_error('Your chosen password is invalid!');
			}

			$account->setPassword($new_password);
			$message = '<span class="green">SUCCESS: </span>You have changed your password.';

		} elseif ($action === $this->actionHofName->value) {
			$HoF_name = Request::get('HoF_name');

			DisplayNameValidator::validate($HoF_name);

			//no duplicates
			try {
				$other = Account::getAccountByHofName($HoF_name);
				if (!$account->equals($other)) {
					create_error('Someone is already using that Hall of Fame name!');
				}
			} catch (AccountNotFound) {
				// Proceed, this Hall of Fame name is not in use
			}

			// set the HoF name in account stat
			$account->setHofName($HoF_name);
			$message = '<span class="green">SUCCESS: </span>You have changed your Hall of Fame name.';

		} elseif ($action === $this->actionDiscordId->value) {
			$discordId = Request::get('discord_id');

			if ($discordId === '') {
				$account->setDiscordId(null);
				$message = '<span class="green">SUCCESS: </span>You have deleted your Discord User ID.';
			} else {
				// no duplicates
				try {
					$other = Account::getAccountByDiscordId($discordId);
					if (!$account->equals($other)) {
						create_error('Someone is already using that Discord User ID!');
					}
				} catch (AccountNotFound) {
					// Proceed, this Discord ID is not in use
				}

				$account->setDiscordId($discordId);
				$message = '<span class="green">SUCCESS: </span>You have changed your Discord User ID.';
			}

		} elseif ($action === $this->actionIrcNick->value) {
			$ircNick = Request::get('irc_nick');

			// here you can delete your registered irc nick
			if ($ircNick === '') {
				$account->setIrcNick(null);
				$message = '<span class="green">SUCCESS: </span>You have deleted your irc nick.';
			} else {
				// Disallow control characters and spaces
				if (!ctype_graph($ircNick)) {
					create_error('Your IRC Nick may only contain visible printed characters!');
				}

				// no duplicates
				try {
					$other = Account::getAccountByIrcNick($ircNick);
					if (!$account->equals($other)) {
						create_error('Someone is already using that IRC nick!');
					}
				} catch (AccountNotFound) {
					// Proceed, this IRC nick is not in use
				}

				// save irc nick in db and set message
				$account->setIrcNick($ircNick);
				$message = '<span class="green">SUCCESS: </span>You have changed your irc nick.';
			}

		} elseif ($action === $this->actionTimezone->value) {
			$timez = Request::getInt('timez');

			$db = Database::getInstance();
			$db->update(
				'account',
				['offset' => $timez],
				['account_id' => $account->getAccountID()],
			);
			$message = '<span class="green">SUCCESS: </span>You have changed your time offset.';

		} elseif ($action === $this->actionDateFormat->value) {
			$account->setDateFormat(Request::get('dateformat'));
			$account->setTimeFormat(Request::get('timeformat'));
			$message = '<span class="green">SUCCESS: </span>You have changed your date formats.';

		} elseif ($action === $this->actionShipImages->value) {
			$account->setDisplayShipImages(Request::getBool('images'));
			$message = '<span class="green">SUCCESS: </span>You have changed your ship images preferences.';

		} elseif ($action === $this->actionGalMapCenter->value) {
			$account->setCenterGalaxyMapOnPlayer(Request::getBool('centergalmap'));
			$message = '<span class="green">SUCCESS: </span>You have changed your centering galaxy map preferences.';

		} elseif ($action === $this->actionFontSize->value) {
			$fontsize = Request::getInt('fontsize');
			if ($fontsize < MIN_FONTSIZE_PERCENT) {
				create_error('Minimum font size is ' . MIN_FONTSIZE_PERCENT . '%');
			}
			$account->setFontSize($fontsize);
			$message = '<span class="green">SUCCESS: </span>You have changed your font size.';

		} elseif ($action === $this->actionCss->value) {
			$account->setCssLink(Request::get('csslink'));
			$cssTemplateAndColor = Request::get('template');
			if ($cssTemplateAndColor === 'None') {
				$account->setDefaultCSSEnabled(false);
			} else {
				$account->setDefaultCSSEnabled(true);
				[$cssTemplate, $cssColourScheme] = explode(' - ', $cssTemplateAndColor);
				$account->setTemplate($cssTemplate);
				$account->setColourScheme($cssColourScheme);
			}
			$message = '<span class="green">SUCCESS: </span>You have changed your CSS options.';

		} elseif ($action === $this->actionHotkeys->value) {
			foreach (Account::getDefaultHotkeys() as $hotkey => $binding) {
				$account->setHotkey($hotkey, explode(' ', Request::get($hotkey)));
			}
			$message = '<span class="green">SUCCESS: </span>You have saved your hotkeys.';

		} elseif ($action === $this->actionColors->value) {
			$friendlyColour = Request::get('friendly_color');
			$neutralColour = Request::get('neutral_color');
			$enemyColour = Request::get('enemy_color');

			if (strlen($friendlyColour) === 6) {
				$account->setFriendlyColour($friendlyColour);
			}
			if (strlen($neutralColour) === 6) {
				$account->setNeutralColour($neutralColour);
			}
			if (strlen($enemyColour) === 6) {
				$account->setEnemyColour($enemyColour);
			}
			$message = '<span class="green">SUCCESS: </span> You have updated your colors.';

		} elseif ($action === $this->actionAjax->value) {
			$account->setUseAJAX(!$account->isUseAJAX());
			$status = $account->isUseAJAX() ? 'enabled' : 'disabled';
			$message = '<span class="green">SUCCESS: </span> You have ' . $status . ' AJAX auto-refresh.';

		} else {
			throw new Exception('Unknown action: ' . $action);
		}

		// Update the account in case it has changed
		$account->update();

		$this::getLandingPage($message)->go();
	}

}
