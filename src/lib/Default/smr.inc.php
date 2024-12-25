<?php declare(strict_types=1);

use Nbbc\BBCode;
use Smr\AbstractPlayer;
use Smr\Alliance;
use Smr\Chess\ChessGame;
use Smr\Container\DiContainer;
use Smr\Database;
use Smr\Epoch;
use Smr\Exceptions\UserError;
use Smr\Force;
use Smr\Game;
use Smr\Globals;
use Smr\Messages;
use Smr\Page\Page;
use Smr\Pages\Account\AlbumEdit;
use Smr\Pages\Account\BugReport;
use Smr\Pages\Account\ChangelogView;
use Smr\Pages\Account\ChatJoin;
use Smr\Pages\Account\ContactForm;
use Smr\Pages\Account\Donation;
use Smr\Pages\Account\ErrorDisplay;
use Smr\Pages\Account\GameLeaveProcessor;
use Smr\Pages\Account\GamePlay;
use Smr\Pages\Account\HallOfFameAll;
use Smr\Pages\Account\LogoffProcessor;
use Smr\Pages\Account\Preferences;
use Smr\Pages\Admin\AdminTools;
use Smr\Pages\Player\AllianceInviteAcceptProcessor;
use Smr\Pages\Player\AllianceMotd;
use Smr\Pages\Player\AllianceRoster;
use Smr\Pages\Player\CargoDump;
use Smr\Pages\Player\CombatLogList;
use Smr\Pages\Player\CombatLogViewerVerifyProcessor;
use Smr\Pages\Player\CurrentSector;
use Smr\Pages\Player\DeathProcessor;
use Smr\Pages\Player\ForcesDrop;
use Smr\Pages\Player\ForcesDropProcessor;
use Smr\Pages\Player\ForcesList;
use Smr\Pages\Player\GalacticPost\CurrentEditionProcessor;
use Smr\Pages\Player\HardwareConfigure;
use Smr\Pages\Player\MessageView;
use Smr\Pages\Player\NewbieWarningProcessor;
use Smr\Pages\Player\NewsReadCurrent;
use Smr\Pages\Player\Rankings\PlayerExperience;
use Smr\Pages\Player\SearchForTrader;
use Smr\Pages\Player\SearchForTraderResult;
use Smr\Pages\Player\WeaponReorder;
use Smr\Planet;
use Smr\Player;
use Smr\Port;
use Smr\Race;
use Smr\Sector;
use Smr\SectorLock;
use Smr\Session;
use Smr\Ship;
use Smr\Template;
use Smr\VoteLink;
use Smr\VoteSite;
use Smr\WeightedRandom;

function parseBoolean(mixed $check): bool {
	// Only negative strings are not implicitly converted to the correct bool
	if (is_string($check) && (strcasecmp($check, 'NO') === 0 || strcasecmp($check, 'FALSE') === 0)) {
		return false;
	}
	return (bool)$check;
}

function linkCombatLog(int $logID): string {
	$container = new CombatLogViewerVerifyProcessor($logID);
	return '<a href="' . $container->href() . '"><img src="images/notify.gif" width="14" height="11" border="0" title="View the combat log" /></a>';
}

/**
 * Converts a BBCode tag into some other text depending on the tag and value.
 * This is called in two stages: first with action BBCODE_CHECK (where the
 * returned value must be a boolean) and second, if the first check passes,
 * with action BBCODE_OUTPUT.
 *
 * @param array<string, string> $tagParams
 */
function smrBBCode(BBCode $bbParser, int $action, string $tagName, string $default, array $tagParams, string $tagContent): bool|string {
	global $overrideGameID, $disableBBLinks;
	$session = Session::getInstance();
	try {
		switch ($tagName) {
			case 'combatlog':
				if ($action === BBCode::BBCODE_CHECK) {
					return is_numeric($default);
				}
				$logID = (int)$default;
				return linkCombatLog($logID);

			case 'player':
				if ($action === BBCode::BBCODE_CHECK) {
					return is_numeric($default);
				}
				$playerID = (int)$default;
				$bbPlayer = Player::getPlayerByPlayerID($playerID, $overrideGameID);
				$showAlliance = isset($tagParams['showalliance']) ? parseBoolean($tagParams['showalliance']) : false;
				if ($disableBBLinks === false && $overrideGameID === $session->getGameID()) {
					return $bbPlayer->getLinkedDisplayName($showAlliance);
				}
				return $bbPlayer->getDisplayName($showAlliance);

			case 'alliance':
				if ($action === BBCode::BBCODE_CHECK) {
					return is_numeric($default);
				}
				$allianceID = (int)$default;
				$alliance = Alliance::getAlliance($allianceID, $overrideGameID);
				if ($disableBBLinks === false && $overrideGameID === $session->getGameID()) {
					if ($session->hasGame() && $alliance->getAllianceID() === $session->getPlayer()->getAllianceID()) {
						$container = new AllianceMotd($alliance->getAllianceID());
					} else {
						$container = new AllianceRoster($alliance->getAllianceID());
					}
					return create_link($container, $alliance->getAllianceDisplayName());
				}
				return $alliance->getAllianceDisplayName();

			case 'race':
				$raceNameID = $default;
				foreach (Race::getAllNames() as $raceID => $raceName) {
					if ((is_numeric($raceNameID) && (int)$raceNameID === $raceID)
						|| $raceNameID === $raceName) {
						if ($action === BBCode::BBCODE_CHECK) {
							return true;
						}
						$linked = $disableBBLinks === false && $overrideGameID === $session->getGameID();
						$player = $session->hasGame() ? $session->getPlayer() : null;
						return AbstractPlayer::getColouredRaceNameOrDefault($raceID, $player, $linked);
					}
				}
				break;

			case 'servertimetouser':
				if ($action === BBCode::BBCODE_CHECK) {
					return true;
				}
				$time = strtotime($default);
				if ($time !== false) {
					$time += $session->getAccount()->getOffset() * 3600;
					return date($session->getAccount()->getDateTimeFormat(), $time);
				}
				break;

			case 'chess':
				if ($action === BBCode::BBCODE_CHECK) {
					return is_numeric($default);
				}
				$chessGameID = (int)$default;
				$chessGame = ChessGame::getChessGame($chessGameID);
				return '<a href="' . $chessGame->getPlayGameHREF() . '">chess game (' . $chessGame->getChessGameID() . ')</a>';

			case 'sector':
				if ($action === BBCode::BBCODE_CHECK) {
					return is_numeric($default);
				}

				$sectorID = (int)$default;
				$sectorTag = '<span class="sectorColour">#' . $sectorID . '</span>';

				if ($disableBBLinks === false
					&& $session->hasGame()
					&& $session->getGameID() === $overrideGameID
					&& Sector::sectorExists($overrideGameID, $sectorID)) {
					return '<a href="' . Globals::getPlotCourseHREF($session->getPlayer()->getSectorID(), $sectorID) . '">' . $sectorTag . '</a>';
				}
				return $sectorTag;

			case 'join_alliance':
				if ($action === BBCode::BBCODE_CHECK) {
					return is_numeric($default);
				}
				$allianceID = (int)$default;
				$alliance = Alliance::getAlliance($allianceID, $overrideGameID);
				$container = new AllianceInviteAcceptProcessor($allianceID);
				return '<div class="buttonA"><a class="buttonA" href="' . $container->href() . '">Join ' . $alliance->getAllianceDisplayName() . '</a></div>';
		}
	} catch (Throwable) {
		// If there's an error, we will silently display the original text
	}
	if ($action === BBCode::BBCODE_CHECK) {
		return false;
	}
	return htmlspecialchars($tagParams['_tag']) . $tagContent . htmlspecialchars($tagParams['_endtag']);
}

function inify(string $text): string {
	return str_replace(',', '', html_entity_decode($text));
}

function bbify(string $message, ?int $gameID = null, bool $noLinks = false): string {
	static $bbParser;
	if (!isset($bbParser)) {
		$bbParser = new BBCode();
		$bbParser->setEnableSmileys(false);
		$bbParser->removeRule('wiki');
		$bbParser->removeRule('img');
		$bbParser->setURLTarget('_blank');
		$bbParser->setURLTargetable('override');
		$bbParser->setEscapeContent(false); // don't escape HTML, needed for News etc.

		// Add [verbatim] tag, needed for displaying other tags
		$bbParser->addRule('verbatim', [
			'content' => BBCode::BBCODE_VERBATIM,
			'simple_start' => '<tt>',
			'simple_end' => '</tt>',
			'allow_in' => ['block'],
		]);

		$smrRule = [
				'mode' => BBCode::BBCODE_MODE_CALLBACK,
				'method' => 'smrBBCode',
				'class' => 'link',
				'allow_in' => ['listitem', 'block', 'columns', 'inline'],
				'end_tag' => BBCode::BBCODE_PROHIBIT,
				'content' => BBCode::BBCODE_PROHIBIT,
			];
		$bbParser->addRule('combatlog', $smrRule);
		$bbParser->addRule('player', $smrRule);
		$bbParser->addRule('alliance', $smrRule);
		$bbParser->addRule('race', $smrRule);
		$bbParser->addRule('servertimetouser', $smrRule);
		$bbParser->addRule('chess', $smrRule);
		$bbParser->addRule('sector', $smrRule);
		$bbParser->addRule('join_alliance', $smrRule);
	}

	global $overrideGameID;
	if ($gameID === null) {
		$overrideGameID = Session::getInstance()->getGameID();
	} else {
		$overrideGameID = $gameID;
	}

	global $disableBBLinks;
	$disableBBLinks = $noLinks;

	if (str_contains($message, '[')) { //We have BBCode so let's do a full parse.
		$message = $bbParser->parse($message);
	} else { //Otherwise just convert newlines
		$message = nl2br($message, true);
	}
	return $message;
}

function create_error(string $message): never {
	throw new UserError($message);
}

function handleUserError(string $message): never {
	if ($_SERVER['SCRIPT_NAME'] !== LOADER_URI) {
		header('Location: /error.php?msg=' . urlencode($message));
		exit;
	}

	// If we're throwing an error, we don't care what data was stored in the
	// Template from the original page.
	DiContainer::getContainer()->reset(Template::class);

	$session = Session::getInstance();
	if ($session->hasGame()) {
		$errorMsg = '<span class="red bold">ERROR: </span>' . $message;
		$container = new CurrentSector(errorMessage: $errorMsg);
	} else {
		$container = new ErrorDisplay(message: $message);
	}

	if ($session->ajax) {
		// To avoid the page just not refreshing when an error is encountered
		// during ajax updates, use javascript to auto-redirect to the
		// appropriate error page.
		$errorHREF = $container->href();
		// json_encode the HREF as a safety precaution
		$template = Template::getInstance();
		$template->addJavascriptForAjax('EVAL', 'location.href = ' . json_encode($errorHREF, JSON_THROW_ON_ERROR));
	}
	$container->go();
}

function create_link(Page|string $container, string $text, ?string $class = null): string {
	return '<a' . ($class === null ? '' : ' class="' . $class . '"') . ' href="' . (is_string($container) ? $container : $container->href()) . '">' . $text . '</a>';
}

function create_submit_link(Page $container, string $text): string {
	return '<a href="' . $container->href() . '" class="submitStyle">' . $text . '</a>';
}

function get_colored_text_range(float $value, int $maxValue, ?string $text = null, int $minValue = 0): string {
	if ($text === null) {
		$text = number_format($value);
	}
	if ($maxValue - $minValue === 0) {
		return $text;
	}
	$normalisedValue = IRound(510 * max(0, min($maxValue, $value) - $minValue) / ($maxValue - $minValue)) - 255;
	if ($normalisedValue < 0) {
		$r_component = 'ff';
		$g_component = dechex(255 + $normalisedValue);
		if (strlen($g_component) === 1) {
			$g_component = '0' . $g_component;
		}
	} elseif ($normalisedValue > 0) {
		$g_component = 'ff';
		$r_component = dechex(255 - $normalisedValue);
		if (strlen($r_component) === 1) {
			$r_component = '0' . $r_component;
		}
	} else {
		$r_component = 'ff';
		$g_component = 'ff';
	}
	$colour = $r_component . $g_component . '00';
	return '<span style="color:#' . $colour . '">' . $text . '</span>';
}

function get_colored_text(float $value, ?string $text = null): string {
	return get_colored_text_range($value, 300, $text, -300);
}

function word_filter(string $string): string {
	static $search, $replace;

	if (!is_array($search) && !is_array($replace)) {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT word_value, word_replacement FROM word_filter');
		$search = [];
		$replace = [];
		foreach ($dbResult->records() as $dbRecord) {
			$search[] = $dbRecord->getString('word_value');
			$replace[] = $dbRecord->getString('word_replacement');
		}
	}

	return str_ireplace($search, $replace, $string);
}

// choose correct pluralization based on amount
function pluralise(int|float $amount, string $word, bool $includeAmount = true): string {
	$result = $word;
	if ((float)$amount !== 1.) {
		$result .= 's';
	}
	if ($includeAmount) {
		$result = $amount . ' ' . $result;
	}
	return $result;
}

/**
 * This function is a hack around the old style http forward mechanism.
 * It is also responsible for setting most of the global variables
 * (see loader.php for the initialization of the globals).
 */
function do_voodoo(): never {
	$session = Session::getInstance();
	$var = $session->getCurrentVar();

	// create account object
	$account = $session->getAccount();

	if ($session->hasGame()) {
		// Get the nominal player information (this may change after locking).
		// We don't force a reload here in case we don't need to lock.
		$player = $session->getPlayer();

		if (!$session->ajax //AJAX should never do anything that requires a lock.
			//&& ($var->file == 'current_sector.php' || $var->file == 'map_local.php') //Neither should CS or LM and they gets loaded a lot so should reduce lag issues with big groups.
		) {
			// We skip locking if we've already failed to display error page
			$lock = SectorLock::getInstance();
			if (!$lock->hasFailed() && $lock->acquireForPlayer($player)) {
				// Reload var info in case it changed between grabbing lock.
				$session->fetchVarInfo();
				if ($session->hasCurrentVar() === false) {
					if (ENABLE_DEBUG) {
						$db = Database::getInstance();
						$db->insert('debug', [
							'debug_type' => 'SPAM',
							'account_id' => $account->getAccountID(),
							'value' => 0,
							'value_2' => 0,
						]);
					}
					throw new UserError('Please do not spam click!');
				}
				$var = $session->getCurrentVar();

				// Reload player now that we have a lock.
				$player = $session->getPlayer(true);
				if ($player->getSectorID() !== $lock->getSectorID()) {
					// Player sector changed after reloading! Release lock and try again.
					$lock->release();
					do_voodoo();
				}
			}
		}

		// update turns on that player
		$player->updateTurns();

		// Check if we need to redirect to a different page
		if (!$var->skipRedirect && !$session->ajax) {
			if ($player->getGame()->hasEnded()) {
				(new GameLeaveProcessor(new GamePlay(errorMessage: 'The game has ended.')))->go();
			}
			if ($player->isDead()) {
				(new DeathProcessor())->go();
			}
			if ($player->getNewbieWarning() && $player->getNewbieTurns() <= NEWBIE_TURNS_WARNING_LIMIT) {
				(new NewbieWarningProcessor())->go();
			}
		}
	}

	// Execute the engine files.
	// This is where the majority of the page-specific work is performed.
	$var->process();

	// Populate the template
	$template = Template::getInstance();
	if (isset($player)) {
		$template->assign('UnderAttack', $var->showUnderAttack($player, $session->ajax));
	}

	//Nothing below this point should require the lock.
	saveAllAndReleaseLock();

	$template->assign('TemplateBody', $var->file);
	if (isset($player)) {
		$template->assign('ThisSector', $player->getSector());
		$template->assign('ThisPlayer', $player);
		$template->assign('ThisShip', $player->getShip());
	}
	$template->assign('ThisAccount', $account);
	$template->assign('ExtraCSSLink', $account->getCssLink());

	doSkeletonAssigns($template);

	// Set ajax refresh time
	$ajaxRefresh = $account->isUseAJAX();
	if ($ajaxRefresh) {
		// If we can refresh, specify the refresh interval in millisecs
		if (isset($player) && $player->canFight()) {
			$ajaxRefresh = AJAX_UNPROTECTED_REFRESH_TIME;
		} else {
			$ajaxRefresh = AJAX_DEFAULT_REFRESH_TIME;
		}
	}
	$template->assign('AJAX_ENABLE_REFRESH', $ajaxRefresh);

	$template->display('skeleton.php', $session->ajax);

	$session->update();

	exit;
}

function saveAllAndReleaseLock(bool $updateSession = true): void {
	// Only save if we have a lock.
	$lock = SectorLock::getInstance();
	if ($lock->isActive()) {
		Sector::saveSectors();
		Ship::saveShips();
		Player::savePlayers();
		Force::saveForces();
		Port::savePorts();
		Planet::savePlanets();
		WeightedRandom::saveWeightedRandoms();
		if ($updateSession) {
			//Update session here to make sure current page $var is up to date before releasing lock.
			Session::getInstance()->update();
		}
		$lock->release();
	}
}

function doTickerAssigns(Template $template, AbstractPlayer $player, Database $db): void {
	//any ticker news?
	if ($player->hasTickers()) {
		$ticker = [];
		$max = Epoch::time() - 60;
		$dateFormat = $player->getAccount()->getDateTimeFormat();
		if ($player->hasTicker('NEWS')) {
			//get recent news (5 mins)
			$dbResult = $db->read('SELECT time,news_message FROM news WHERE game_id = :game_id AND time >= :max_time ORDER BY time DESC LIMIT 4', [
				'game_id' => $db->escapeNumber($player->getGameID()),
				'max_time' => $db->escapeNumber($max),
			]);
			foreach ($dbResult->records() as $dbRecord) {
				$ticker[] = [
					'Time' => date($dateFormat, $dbRecord->getInt('time')),
					'Message' => $dbRecord->getString('news_message'),
				];
			}
		}
		if ($player->hasTicker('SCOUT')) {
			$dbResult = $db->read('SELECT message_text,send_time FROM message
						WHERE ' . AbstractPlayer::SQL . '
						AND message_type_id = :message_type_id
						AND send_time >= :max_time
						AND sender_id NOT IN (SELECT account_id FROM player_has_ticker WHERE type = :type AND expires > :now AND game_id = :game_id) AND receiver_delete = \'FALSE\'
						ORDER BY send_time DESC
						LIMIT 4', [
				...$player->SQLID,
				'message_type_id' => $db->escapeNumber(MSG_SCOUT),
				'max_time' => $db->escapeNumber($max),
				'type' => $db->escapeString('BLOCK'),
				'now' => $db->escapeNumber(Epoch::time()),
			]);
			foreach ($dbResult->records() as $dbRecord) {
				$ticker[] = [
					'Time' => date($dateFormat, $dbRecord->getInt('send_time')),
					'Message' => $dbRecord->getString('message_text'),
				];
			}
		}
		$template->assign('Ticker', $ticker);
	}
}

function doSkeletonAssigns(Template $template): void {
	$session = Session::getInstance();
	$account = $session->getAccount();
	$db = Database::getInstance();

	$template->assign('CSSLink', $account->getCssUrl());
	$template->assign('CSSColourLink', $account->getCssColourUrl());

	$template->assign('FontSize', $account->getFontSize() - 20);
	$template->assign('timeDisplay', date($account->getDateTimeFormatSplit(), Epoch::time()));

	$container = new HallOfFameAll();
	$template->assign('HallOfFameLink', $container->href());

	$template->assign('AccountID', $account->getAccountID());
	$template->assign('PlayGameLink', (new GameLeaveProcessor(new GamePlay()))->href());

	$template->assign('LogoutLink', (new LogoffProcessor())->href());

	$container = new GameLeaveProcessor(new AdminTools());
	$template->assign('AdminToolsLink', $container->href());

	$container = new Preferences();
	$template->assign('PreferencesLink', $container->href());

	$container = new AlbumEdit();
	$template->assign('EditPhotoLink', $container->href());

	$container = new BugReport();
	$template->assign('ReportABugLink', $container->href());

	$container = new ContactForm();
	$template->assign('ContactFormLink', $container->href());

	$container = new ChatJoin();
	$template->assign('IRCLink', $container->href());

	$container = new Donation();
	$template->assign('DonateLink', $container->href());

	if ($session->hasGame()) {
		$player = $session->getPlayer();
		$template->assign('GameName', Game::getGame($session->getGameID())->getName());
		$template->assign('GameID', $session->getGameID());

		$template->assign('PlotCourseLink', Globals::getPlotCourseHREF());

		$template->assign('TraderLink', Globals::getTraderStatusHREF());

		$template->assign('PoliticsLink', Globals::getCouncilHREF($player->getRaceID()));

		$container = new CombatLogList();
		$template->assign('CombatLogsLink', $container->href());

		$template->assign('PlanetLink', Globals::getPlanetListHREF($player->getAllianceID()));

		$container = new ForcesList();
		$template->assign('ForcesLink', $container->href());

		$template->assign('MessagesLink', Globals::getViewMessageBoxesHREF());

		$container = new NewsReadCurrent();
		$template->assign('ReadNewsLink', $container->href());

		$container = new CurrentEditionProcessor();
		$template->assign('GalacticPostLink', $container->href());

		$container = new SearchForTrader();
		$template->assign('SearchForTraderLink', $container->href());

		$container = new PlayerExperience();
		$template->assign('RankingsLink', $container->href());

		$container = new HallOfFameAll($player->getGameID());
		$template->assign('CurrentHallOfFameLink', $container->href());

		$unreadMessages = [];
		$dbResult = $db->read('SELECT message_type_id,COUNT(*) FROM player_has_unread_messages WHERE ' . AbstractPlayer::SQL . ' GROUP BY message_type_id', $player->SQLID);
		foreach ($dbResult->records() as $dbRecord) {
			$messageTypeID = $dbRecord->getInt('message_type_id');
			$container = new MessageView($messageTypeID);
			$unreadMessages[] = [
				'href' => $container->href(),
				'num' => $dbRecord->getInt('COUNT(*)'),
				'alt' => Messages::getMessageTypeNames($messageTypeID),
				'img' => Messages::getMessageTypeImage($messageTypeID),
			];
		}
		$template->assign('UnreadMessages', $unreadMessages);

		$container = new SearchForTraderResult($player->getPlayerID());
		$template->assign('PlayerNameLink', $container->href());

		$template->assign('PlayerInvisible', $player->isObserver());

		// ******* Hardware *******
		$container = new HardwareConfigure();
		$template->assign('HardwareLink', $container->href());

		// ******* Forces *******
		$template->assign('ForceDropLink', (new ForcesDrop())->href());

		$ship = $player->getShip();
		$var = Session::getInstance()->getCurrentVar();
		if ($ship->hasMines()) {
			$container = new ForcesDropProcessor($player->getAccountID(), referrer: $var::class, dropMines: 1);
			$template->assign('DropMineLink', $container->href());
		}
		if ($ship->hasCDs()) {
			$container = new ForcesDropProcessor($player->getAccountID(), referrer: $var::class, dropCDs: 1);
			$template->assign('DropCDLink', $container->href());
		}
		if ($ship->hasSDs()) {
			$container = new ForcesDropProcessor($player->getAccountID(), referrer: $var::class, dropSDs: 1);
			$template->assign('DropSDLink', $container->href());
		}

		$template->assign('CargoJettisonLink', (new CargoDump())->href());

		$template->assign('WeaponReorderLink', (new WeaponReorder())->href());

	}

	// ------- VOTING --------
	$voteLinks = [];
	foreach (VoteSite::cases() as $site) {
		$link = new VoteLink($site, $account->getAccountID(), $session->getGameID());
		$voteLinks[] = [
			'img' => $link->getImg(),
			'url' => $link->getUrl(),
			'sn' => $link->getSN(),
		];
	}
	$template->assign('VoteLinks', $voteLinks);

	// Determine the minimum time until the next vote across all sites
	$minVoteWait = VoteLink::getMinTimeUntilFreeTurns($account->getAccountID(), $session->getGameID());
	$template->assign('TimeToNextVote', in_time_or_now($minVoteWait, true));

	// ------- VERSION --------
	$dbResult = $db->read('SELECT * FROM version ORDER BY went_live DESC LIMIT 1');
	$version = '';
	if ($dbResult->hasRecord()) {
		$dbRecord = $dbResult->record();
		$container = new ChangelogView();
		$version = create_link($container, 'v' . $dbRecord->getInt('major_version') . '.' . $dbRecord->getInt('minor_version') . '.' . $dbRecord->getInt('patch_level'));
	}

	$template->assign('Version', $version);
	$template->assign('CurrentYear', date('Y', Epoch::time()));
}

/**
 * Join a list of items into a grammatically-correct sentence fragment.
 *
 * @param array<string> $items
 */
function format_list(array $items): string {
	if (count($items) === 0) {
		$result = '';
	} elseif (count($items) === 1) {
		$result = $items[0];
	} else {
		$result = implode(', ', array_slice($items, 0, -1)) . ' and ' . end($items);
	}
	return $result;
}

/**
 * Convert an integer number of seconds into a human-readable time for
 * grammatically-correct use in a sentence, i.e. prefixed with "in" when
 * the amount is positive.
 */
function in_time_or_now(int $seconds, bool $short = false): string {
	$result = format_time($seconds, $short);
	if ($seconds > 0) {
		$result = 'in ' . $result;
	}
	return $result;
}

/**
 * Convert an integer number of seconds into a human-readable time.
 * Seconds are omitted to avoid frequent and disruptive ajax updates.
 * Use short=true to use 1-letter units (e.g. "1h and 3m").
 * If seconds is negative, will append "ago" to the result.
 * If seconds is zero, will return only "now".
 * If seconds is <60, will prefix "less than" or "<" (HTML-safe).
 */
function format_time(int $seconds, bool $short = false): string {
	if ($seconds === 0) {
		return 'now';
	}

	if ($seconds < 0) {
		$past = true;
		$seconds = abs($seconds);
	} else {
		$past = false;
	}

	$minutes = ceil($seconds / 60);
	$hours = 0;
	$days = 0;
	$weeks = 0;
	if ($minutes >= 60) {
		$hours = floor($minutes / 60);
		$minutes %= 60;
	}
	if ($hours >= 24) {
		$days = floor($hours / 24);
		$hours %= 24;
	}
	if ($days >= 7) {
		$weeks = floor($days / 7);
		$days %= 7;
	}
	$times = [
		'week' => $weeks,
		'day' => $days,
		'hour' => $hours,
		'minute' => $minutes,
	];
	$parts = [];
	foreach ($times as $unit => $amount) {
		if ($amount > 0) {
			if ($short) {
				$parts[] = $amount . $unit[0];
			} else {
				$parts[] = pluralise($amount, $unit);
			}
		}
	}

	// e.g. 5h, 10m and 30s
	$result = format_list($parts);

	if ($seconds < 60) {
		$result = ($short ? '&lt;' : 'less than ') . $result;
	}

	if ($past) {
		$result .= ' ago';
	}
	return $result;
}

function number_colour_format(float $number, bool $justSign = false): string {
	$formatted = '<span';
	if ($number > 0) {
		$formatted .= ' class="green">+';
	} elseif ($number < 0) {
		$formatted .= ' class="red">-';
	} else {
		$formatted .= '>';
	}
	if ($justSign === false) {
		$decimalPlaces = 0;
		$pos = strpos((string)$number, '.');
		if ($pos !== false) {
			$decimalPlaces = strlen(substr((string)$number, $pos + 1));
		}
		$formatted .= number_format(abs($number), $decimalPlaces);
	}
	$formatted .= '</span>';
	return $formatted;
}

/**
 * Randomly choose an array key weighted by the array values.
 * Probabilities are relative to the total weight. For example:
 *
 * array(
 *    'A' => 1, // 10% chance
 *    'B' => 3, // 30% chance
 *    'C' => 6, // 60% chance
 * );
 *
 * @template T of array-key
 * @param array<T, float> $choices
 * @return T
 */
function getWeightedRandom(array $choices): string|int {
	// Normalize the weights so that their sum is 1
	$sumWeight = array_sum($choices);
	foreach ($choices as $key => $weight) {
		$choices[$key] = $weight / $sumWeight;
	}

	// Generate a random number between 0 and 1
	$rand = rand() / getrandmax();

	// Subtract weights from the random number until it is negative,
	// then return the key associated with that weight.
	foreach ($choices as $key => $weight) {
		$rand -= $weight;
		if ($rand <= 0) {
			return $key;
		}
	}
	throw new Exception('Internal error computing weights');
}
