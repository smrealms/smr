<?php declare(strict_types=1);

function parseBoolean(mixed $check): bool {
	// Only negative strings are not implicitly converted to the correct bool
	if (is_string($check) && (strcasecmp($check, 'NO') == 0 || strcasecmp($check, 'FALSE') == 0)) {
		return false;
	}
	return (bool)$check;
}

function linkCombatLog(int $logID): string {
	$container = Page::create('combat_log_viewer_verify.php');
	$container['log_id'] = $logID;
	return '<a href="' . $container->href() . '"><img src="images/notify.gif" width="14" height="11" border="0" title="View the combat log" /></a>';
}

/**
 * Converts a BBCode tag into some other text depending on the tag and value.
 * This is called in two stages: first with action BBCODE_CHECK (where the
 * returned value must be a boolean) and second, if the first check passes,
 * with action BBCODE_OUTPUT.
 */
function smrBBCode($bbParser, $action, $tagName, $default, $tagParams, $tagContent) {
	global $overrideGameID, $disableBBLinks;
	$session = Smr\Session::getInstance();
	try {
		switch ($tagName) {
			case 'combatlog':
				if ($action == \Nbbc\BBCode::BBCODE_CHECK) {
					return is_numeric($default);
				}
				$logID = (int)$default;
				return linkCombatLog($logID);

			case 'player':
				if ($action == \Nbbc\BBCode::BBCODE_CHECK) {
					return is_numeric($default);
				}
				$playerID = (int)$default;
				$bbPlayer = SmrPlayer::getPlayerByPlayerID($playerID, $overrideGameID);
				$showAlliance = isset($tagParams['showalliance']) ? parseBoolean($tagParams['showalliance']) : false;
				if ($disableBBLinks === false && $overrideGameID == $session->getGameID()) {
					return $bbPlayer->getLinkedDisplayName($showAlliance);
				}
				return $bbPlayer->getDisplayName($showAlliance);

			case 'alliance':
				if ($action == \Nbbc\BBCode::BBCODE_CHECK) {
					return is_numeric($default);
				}
				$allianceID = (int)$default;
				$alliance = SmrAlliance::getAlliance($allianceID, $overrideGameID);
				if ($disableBBLinks === false && $overrideGameID == $session->getGameID()) {
					$container = Page::create('skeleton.php');
					$container['alliance_id'] = $alliance->getAllianceID();
					if ($session->hasGame() && $alliance->getAllianceID() == $session->getPlayer()->getAllianceID()) {
						$container['body'] = 'alliance_mod.php';
					} else {
						$container['body'] = 'alliance_roster.php';
					}
					return create_link($container, $alliance->getAllianceDisplayName());
				}
				return $alliance->getAllianceDisplayName();

			case 'race':
				$raceNameID = $default;
				foreach (Smr\Race::getAllNames() as $raceID => $raceName) {
					if ((is_numeric($raceNameID) && $raceNameID == $raceID)
						|| $raceNameID == $raceName) {
						if ($action == \Nbbc\BBCode::BBCODE_CHECK) {
							return true;
						}
						$linked = $disableBBLinks === false && $overrideGameID == $session->getGameID();
						$player = $session->hasGame() ? $session->getPlayer() : null;
						return AbstractSmrPlayer::getColouredRaceNameOrDefault($raceID, $player, $linked);
					}
				}
				break;

			case 'servertimetouser':
				if ($action == \Nbbc\BBCode::BBCODE_CHECK) {
					return true;
				}
				$time = strtotime($default);
				if ($time !== false) {
					$time += $session->getAccount()->getOffset() * 3600;
					return date($session->getAccount()->getDateTimeFormat(), $time);
				}
				break;

			case 'chess':
				if ($action == \Nbbc\BBCode::BBCODE_CHECK) {
					return is_numeric($default);
				}
				$chessGameID = (int)$default;
				$chessGame = Smr\Chess\ChessGame::getChessGame($chessGameID);
				return '<a href="' . $chessGame->getPlayGameHREF() . '">chess game (' . $chessGame->getChessGameID() . ')</a>';

			case 'sector':
				if ($action == \Nbbc\BBCode::BBCODE_CHECK) {
					return is_numeric($default);
				}

				$sectorID = (int)$default;
				$sectorTag = '<span class="sectorColour">#' . $sectorID . '</span>';

				if ($disableBBLinks === false
					&& $session->hasGame()
					&& $session->getGameID() == $overrideGameID
					&& SmrSector::sectorExists($overrideGameID, $sectorID)) {
					return '<a href="' . Globals::getPlotCourseHREF($session->getPlayer()->getSectorID(), $sectorID) . '">' . $sectorTag . '</a>';
				}
				return $sectorTag;

			case 'join_alliance':
				if ($action == \Nbbc\BBCode::BBCODE_CHECK) {
					return is_numeric($default);
				}
				$allianceID = (int)$default;
				$alliance = SmrAlliance::getAlliance($allianceID, $overrideGameID);
				$container = Page::create('alliance_invite_accept_processing.php');
				$container['alliance_id'] = $alliance->getAllianceID();
				return '<div class="buttonA"><a class="buttonA" href="' . $container->href() . '">Join ' . $alliance->getAllianceDisplayName() . '</a></div>';
		}
	} catch (Throwable) {
		// If there's an error, we will silently display the original text
	}
	if ($action == \Nbbc\BBCode::BBCODE_CHECK) {
		return false;
	}
	return htmlspecialchars($tagParams['_tag']) . $tagContent . htmlspecialchars($tagParams['_endtag']);
}

function inify(string $text): string {
	return str_replace(',', '', html_entity_decode($text));
}

function bbifyMessage(string $message, bool $noLinks = false): string {
	static $bbParser;
	if (!isset($bbParser)) {
		$bbParser = new \Nbbc\BBCode();
		$bbParser->setEnableSmileys(false);
		$bbParser->removeRule('wiki');
		$bbParser->removeRule('img');
		$bbParser->setURLTarget('_blank');
		$bbParser->setURLTargetable('override');
		$bbParser->setEscapeContent(false); // don't escape HTML, needed for News etc.
		$smrRule = [
				'mode' => \Nbbc\BBCode::BBCODE_MODE_CALLBACK,
				'method' => 'smrBBCode',
				'class' => 'link',
				'allow_in' => ['listitem', 'block', 'columns', 'inline'],
				'end_tag' => \Nbbc\BBCode::BBCODE_PROHIBIT,
				'content' => \Nbbc\BBCode::BBCODE_PROHIBIT,
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

	global $disableBBLinks;
	$disableBBLinks = $noLinks;

	if (strpos($message, '[') !== false) { //We have BBCode so let's do a full parse.
		$message = $bbParser->parse($message);
	} else { //Otherwise just convert newlines
		$message = nl2br($message, true);
	}
	return $message;
}

function create_error(string $message): never {
	if ($_SERVER['REQUEST_URI'] !== LOADER_URI) {
		header('Location: /error.php?msg=' . urlencode($message));
		exit;
	}
	$container = Page::create('skeleton.php', 'error.php');
	$container['message'] = $message;
	if (USING_AJAX) {
		// To avoid the page just not refreshing when an error is encountered
		// during ajax updates, use javascript to auto-redirect to the
		// appropriate error page.
		$errorHREF = $container->href();
		// json_encode the HREF as a safety precaution
		$template = Smr\Template::getInstance();
		$template->addJavascriptForAjax('EVAL', 'location.href = ' . json_encode($errorHREF));
	}
	$container->go();
}

function create_link(Page|string $container, string $text, string $class = null): string {
	return '<a' . ($class === null ? '' : ' class="' . $class . '"') . ' href="' . (is_string($container) ? $container : $container->href()) . '">' . $text . '</a>';
}

function create_submit_link(Page $container, string $text): string {
	return '<a href="' . $container->href() . '" class="submitStyle">' . $text . '</a>';
}

function get_colored_text_range(float $value, float $maxValue, string $text = null, float $minValue = 0): string {
	if ($text === null) {
		$text = number_format($value);
	}
	if ($maxValue - $minValue == 0) {
		return $text;
	}
	$normalisedValue = IRound(510 * max(0, min($maxValue, $value) - $minValue) / ($maxValue - $minValue)) - 255;
	if ($normalisedValue < 0) {
		$r_component = 'ff';
		$g_component = dechex(255 + $normalisedValue);
		if (strlen($g_component) == 1) {
			$g_component = '0' . $g_component;
		}
	} elseif ($normalisedValue > 0) {
		$g_component = 'ff';
		$r_component = dechex(255 - $normalisedValue);
		if (strlen($r_component) == 1) {
			$r_component = '0' . $r_component;
		}
	} else {
		$r_component = 'ff';
		$g_component = 'ff';
	}
	$colour = $r_component . $g_component . '00';
	return '<span style="color:#' . $colour . '">' . $text . '</span>';
}

function get_colored_text(float $value, string $text = null): string {
	return get_colored_text_range($value, 300, $text, -300);
}

function word_filter(string $string): string {
	static $words;

	if (!is_array($words)) {
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT word_value, word_replacement FROM word_filter');
		$words = [];
		foreach ($dbResult->records() as $dbRecord) {
			$row = $dbRecord->getRow();
			$words[] = ['word_value' => '/' . str_replace('/', '\/', $row['word_value']) . '/i', 'word_replacement' => $row['word_replacement']];
		}
	}

	foreach ($words as $word) {
		$string = preg_replace($word['word_value'], $word['word_replacement'], $string);
	}

	return $string;
}

// choose correct pluralization based on amount
function pluralise(int|float $amount, string $word, bool $includeAmount = true): string {
	$result = $word;
	if ($amount != 1) {
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
	$session = Smr\Session::getInstance();
	$var = $session->getCurrentVar();

	if (!defined('AJAX_CONTAINER')) {
		define('AJAX_CONTAINER', isset($var['AJAX']) && $var['AJAX'] === true);
	}

	if (!AJAX_CONTAINER && USING_AJAX && $session->hasChangedSN()) {
		exit;
	}
	//ob_clean();

	// create account object
	$account = $session->getAccount();

	if ($session->hasGame()) {
		// Get the nominal player information (this may change after locking).
		// We don't force a reload here in case we don't need to lock.
		$player = $session->getPlayer();
		$sectorID = $player->getSectorID();

		if (!USING_AJAX //AJAX should never do anything that requires a lock.
			//&& !isset($var['url']) && ($var['body'] == 'current_sector.php' || $var['body'] == 'map_local.php') //Neither should CS or LM and they gets loaded a lot so should reduce lag issues with big groups.
		) {
			// We skip locking if we've already failed to display error page
			$lock = Smr\SectorLock::getInstance();
			if (!$lock->hasFailed() && $lock->acquireForPlayer($player)) {
				// Reload var info in case it changed between grabbing lock.
				$session->fetchVarInfo();
				if ($session->hasCurrentVar() === false) {
					if (ENABLE_DEBUG) {
						$db = Smr\Database::getInstance();
						$db->insert('debug', [
							'debug_type' => $db->escapeString('SPAM'),
							'account_id' => $db->escapeNumber($account->getAccountID()),
							'value' => 0,
							'value_2' => 0,
						]);
					}
					throw new Smr\Exceptions\UserError('Please do not spam click!');
				}
				$var = $session->getCurrentVar();

				// Reload player now that we have a lock.
				$player = $session->getPlayer(true);
				if ($player->getSectorID() != $sectorID) {
					// Player sector changed after reloading! Release lock and try again.
					$lock->release();
					do_voodoo();
				}
			}
		}

		// update turns on that player
		$player->updateTurns();

		// Check if we need to redirect to a different page
		if (!$var->skipRedirect() && !USING_AJAX) {
			if ($player->getGame()->hasEnded()) {
				Page::create('game_leave_processing.php', 'game_play.php', ['errorMsg' => 'The game has ended.'], skipRedirect: true)->go();
			}
			if ($player->isDead()) {
				Page::create('death_processing.php', skipRedirect: true)->go();
			}
			if ($player->getNewbieWarning() && $player->getNewbieTurns() <= NEWBIE_TURNS_WARNING_LIMIT) {
				Page::create('newbie_warning_processing.php', skipRedirect: true)->go();
			}
		}
	}

	// Execute the engine files.
	// This is where the majority of the page-specific work is performed.
	$var->process();

	// Populate the template
	$template = Smr\Template::getInstance();
	if ($session->hasGame()) {
		$template->assign('UnderAttack', $player->removeUnderAttack());
	}

	//Nothing below this point should require the lock.
	saveAllAndReleaseLock();

	$template->assign('TemplateBody', $var['body']);
	if ($session->hasGame()) {
		$template->assign('ThisSector', $player->getSector());
		$template->assign('ThisPlayer', $player);
		$template->assign('ThisShip', $player->getShip());
	}
	$template->assign('ThisAccount', $account);
	if ($account->getCssLink() != null) {
		$template->assign('ExtraCSSLink', $account->getCssLink());
	}
	doSkeletonAssigns($template);

	// Set ajax refresh time
	$ajaxRefresh = $var['AllowAjax'] ?? true; // hack for bar_gambling_processing.php
	if (!$account->isUseAJAX()) {
		$ajaxRefresh = false;
	}
	if ($ajaxRefresh) {
		// If we can refresh, specify the refresh interval in millisecs
		if ($session->hasGame() && $player->canFight()) {
			$ajaxRefresh = AJAX_UNPROTECTED_REFRESH_TIME;
		} else {
			$ajaxRefresh = AJAX_DEFAULT_REFRESH_TIME;
		}
	}
	$template->assign('AJAX_ENABLE_REFRESH', $ajaxRefresh);

	$template->display($var['url'], USING_AJAX || AJAX_CONTAINER);

	$session->update();

	exit;
}

function saveAllAndReleaseLock($updateSession = true): void {
	// Only save if we have a lock.
	$lock = Smr\SectorLock::getInstance();
	if ($lock->isActive()) {
		SmrSector::saveSectors();
		SmrShip::saveShips();
		SmrPlayer::savePlayers();
		// Skip any caching classes that haven't even been loaded yet
		if (class_exists(SmrForce::class, false)) {
			SmrForce::saveForces();
		}
		if (class_exists(SmrPort::class, false)) {
			SmrPort::savePorts();
		}
		if (class_exists(SmrPlanet::class, false)) {
			SmrPlanet::savePlanets();
		}
		if (class_exists(WeightedRandom::class, false)) {
			WeightedRandom::saveWeightedRandoms();
		}
		if ($updateSession) {
			//Update session here to make sure current page $var is up to date before releasing lock.
			Smr\Session::getInstance()->update();
		}
		$lock->release();
	}
}

function doTickerAssigns(Smr\Template $template, SmrPlayer $player, Smr\Database $db): void {
	//any ticker news?
	if ($player->hasTickers()) {
		$ticker = [];
		$max = Smr\Epoch::time() - 60;
		$dateFormat = $player->getAccount()->getDateTimeFormat();
		if ($player->hasTicker('NEWS')) {
			//get recent news (5 mins)
			$dbResult = $db->read('SELECT time,news_message FROM news WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND time >= ' . $max . ' ORDER BY time DESC LIMIT 4');
			foreach ($dbResult->records() as $dbRecord) {
				$ticker[] = [
					'Time' => date($dateFormat, $dbRecord->getInt('time')),
					'Message' => $dbRecord->getField('news_message'),
				];
			}
		}
		if ($player->hasTicker('SCOUT')) {
			$dbResult = $db->read('SELECT message_text,send_time FROM message
						WHERE account_id=' . $db->escapeNumber($player->getAccountID()) . '
						AND game_id=' . $db->escapeNumber($player->getGameID()) . '
						AND message_type_id=' . $db->escapeNumber(MSG_SCOUT) . '
						AND send_time>=' . $db->escapeNumber($max) . '
						AND sender_id NOT IN (SELECT account_id FROM player_has_ticker WHERE type=' . $db->escapeString('BLOCK') . ' AND expires > ' . $db->escapeNumber(Smr\Epoch::time()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ') AND receiver_delete = \'FALSE\'
						ORDER BY send_time DESC
						LIMIT 4');
			foreach ($dbResult->records() as $dbRecord) {
				$ticker[] = [
					'Time' => date($dateFormat, $dbRecord->getInt('send_time')),
					'Message' => $dbRecord->getField('message_text'),
				];
			}
		}
		$template->assign('Ticker', $ticker);
	}
}

function doSkeletonAssigns(Smr\Template $template): void {
	$session = Smr\Session::getInstance();
	$account = $session->getAccount();
	$db = Smr\Database::getInstance();

	$template->assign('CSSLink', $account->getCssUrl());
	$template->assign('CSSColourLink', $account->getCssColourUrl());

	$template->assign('FontSize', $account->getFontSize() - 20);
	$template->assign('timeDisplay', date($account->getDateTimeFormatSplit(), Smr\Epoch::time()));

	$container = Page::create('skeleton.php');

	if ($session->hasGame()) {
		$player = $session->getPlayer();
		$template->assign('GameName', SmrGame::getGame($session->getGameID())->getName());
		$template->assign('GameID', $session->getGameID());

		$template->assign('PlotCourseLink', Globals::getPlotCourseHREF());

		$template->assign('TraderLink', Globals::getTraderStatusHREF());

		$template->assign('PoliticsLink', Globals::getPoliticsHREF());

		$container['body'] = 'combat_log_list.php';
		$template->assign('CombatLogsLink', $container->href());

		$template->assign('PlanetLink', Globals::getPlanetListHREF($player->getAllianceID()));

		$container['body'] = 'forces_list.php';
		$template->assign('ForcesLink', $container->href());

		$template->assign('MessagesLink', Globals::getViewMessageBoxesHREF());

		$container['body'] = 'news_read_current.php';
		$template->assign('ReadNewsLink', $container->href());

		$container['body'] = 'galactic_post_current.php';
		$template->assign('GalacticPostLink', $container->href());

		$container['body'] = 'trader_search.php';
		$template->assign('SearchForTraderLink', $container->href());

		$container['body'] = 'rankings_player_experience.php';
		$template->assign('RankingsLink', $container->href());

		$container['body'] = 'hall_of_fame_new.php';
		$container['game_id'] = $player->getGameID();
		$template->assign('CurrentHallOfFameLink', $container->href());
	}

	$container = Page::create('skeleton.php', 'hall_of_fame_new.php');
	$template->assign('HallOfFameLink', $container->href());

	$template->assign('AccountID', $account->getAccountID());
	$template->assign('PlayGameLink', Page::create('game_leave_processing.php', 'game_play.php')->href());

	$template->assign('LogoutLink', Page::create('logoff.php')->href());

	$container = Page::create('game_leave_processing.php', 'admin/admin_tools.php');
	$template->assign('AdminToolsLink', $container->href());

	$container = Page::create('skeleton.php', 'preferences.php');
	$template->assign('PreferencesLink', $container->href());

	$container['body'] = 'album_edit.php';
	$template->assign('EditPhotoLink', $container->href());

	$container['body'] = 'bug_report.php';
	$template->assign('ReportABugLink', $container->href());

	$container['body'] = 'contact.php';
	$template->assign('ContactFormLink', $container->href());

	$container['body'] = 'chat_rules.php';
	$template->assign('IRCLink', $container->href());

	$container['body'] = 'donation.php';
	$template->assign('DonateLink', $container->href());

	if ($session->hasGame()) {
		$dbResult = $db->read('SELECT message_type_id,COUNT(*) FROM player_has_unread_messages WHERE ' . $player->getSQL() . ' GROUP BY message_type_id');

		if ($dbResult->hasRecord()) {
			$messages = [];
			foreach ($dbResult->records() as $dbRecord) {
				$messages[$dbRecord->getInt('message_type_id')] = $dbRecord->getInt('COUNT(*)');
			}

			$container = Page::create('skeleton.php', 'message_view.php');

			if (isset($messages[MSG_GLOBAL])) {
				$container['folder_id'] = MSG_GLOBAL;
				$template->assign('MessageGlobalLink', $container->href());
				$template->assign('MessageGlobalNum', $messages[MSG_GLOBAL]);
			}

			if (isset($messages[MSG_PLAYER])) {
				$container['folder_id'] = MSG_PLAYER;
				$template->assign('MessagePersonalLink', $container->href());
				$template->assign('MessagePersonalNum', $messages[MSG_PLAYER]);
			}

			if (isset($messages[MSG_SCOUT])) {
				$container['folder_id'] = MSG_SCOUT;
				$template->assign('MessageScoutLink', $container->href());
				$template->assign('MessageScoutNum', $messages[MSG_SCOUT]);
			}

			if (isset($messages[MSG_POLITICAL])) {
				$container['folder_id'] = MSG_POLITICAL;
				$template->assign('MessagePoliticalLink', $container->href());
				$template->assign('MessagePoliticalNum', $messages[MSG_POLITICAL]);
			}

			if (isset($messages[MSG_ALLIANCE])) {
				$container['folder_id'] = MSG_ALLIANCE;
				$template->assign('MessageAllianceLink', $container->href());
				$template->assign('MessageAllianceNum', $messages[MSG_ALLIANCE]);
			}

			if (isset($messages[MSG_ADMIN])) {
				$container['folder_id'] = MSG_ADMIN;
				$template->assign('MessageAdminLink', $container->href());
				$template->assign('MessageAdminNum', $messages[MSG_ADMIN]);
			}

			if (isset($messages[MSG_CASINO])) {
				$container['folder_id'] = MSG_CASINO;
				$template->assign('MessageCasinoLink', $container->href());
				$template->assign('MessageCasinoNum', $messages[MSG_CASINO]);
			}

			if (isset($messages[MSG_PLANET])) {
				$container = Page::create('planet_msg_processing.php');
				$template->assign('MessagePlanetLink', $container->href());
				$template->assign('MessagePlanetNum', $messages[MSG_PLANET]);
			}
		}

		$container = Page::create('skeleton.php', 'trader_search_result.php');
		$container['player_id'] = $player->getPlayerID();
		$template->assign('PlayerNameLink', $container->href());

		if (is_array(Globals::getHiddenPlayers()) && in_array($player->getAccountID(), Globals::getHiddenPlayers())) {
			$template->assign('PlayerInvisible', true);
		}

		// ******* Hardware *******
		$container = Page::create('skeleton.php', 'configure_hardware.php');

		$template->assign('HardwareLink', $container->href());

		// ******* Forces *******
		$template->assign('ForceDropLink', Page::create('skeleton.php', 'forces_drop.php')->href());

		$ship = $player->getShip();
		if ($ship->hasMines()) {
			$container = Page::create('forces_drop_processing.php');
			$container['owner_id'] = $player->getAccountID();
			$container['drop_mines'] = 1;
			$container->addVar('body', 'referrer');
			$template->assign('DropMineLink', $container->href());
		}
		if ($ship->hasCDs()) {
			$container = Page::create('forces_drop_processing.php');
			$container['owner_id'] = $player->getAccountID();
			$container['drop_combat_drones'] = 1;
			$container->addVar('body', 'referrer');
			$template->assign('DropCDLink', $container->href());
		}

		if ($ship->hasSDs()) {
			$container = Page::create('forces_drop_processing.php');
			$container['owner_id'] = $player->getAccountID();
			$container['drop_scout_drones'] = 1;
			$container->addVar('body', 'referrer');
			$template->assign('DropSDLink', $container->href());
		}

		$template->assign('CargoJettisonLink', Page::create('skeleton.php', 'cargo_dump.php')->href());

		$template->assign('WeaponReorderLink', Page::create('skeleton.php', 'weapon_reorder.php')->href());

	}

	// ------- VOTING --------
	$voteSites = [];
	foreach (Smr\VoteSite::getAllSites($account->getAccountID()) as $site) {
		$voteSites[] = [
			'img' => $site->getLinkImg($session->getGameID()),
			'url' => $site->getLinkUrl($session->getGameID()),
			'sn' => $site->getSN($session->getGameID()),
		];
	}
	$template->assign('VoteSites', $voteSites);

	// Determine the minimum time until the next vote across all sites
	$minVoteWait = Smr\VoteSite::getMinTimeUntilFreeTurns($account->getAccountID());
	if ($minVoteWait <= 0) {
		$template->assign('TimeToNextVote', 'now');
	} else {
		$template->assign('TimeToNextVote', 'in ' . format_time($minVoteWait, true));
	}

	// ------- VERSION --------
	$dbResult = $db->read('SELECT * FROM version ORDER BY went_live DESC LIMIT 1');
	$version = '';
	if ($dbResult->hasRecord()) {
		$dbRecord = $dbResult->record();
		$container = Page::create('skeleton.php', 'changelog_view.php');
		$version = create_link($container, 'v' . $dbRecord->getInt('major_version') . '.' . $dbRecord->getInt('minor_version') . '.' . $dbRecord->getInt('patch_level'));
	}

	$template->assign('Version', $version);
	$template->assign('CurrentYear', date('Y', Smr\Epoch::time()));
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
	if ($seconds == 0) {
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
		$minutes = $minutes % 60;
	}
	if ($hours >= 24) {
		$days = floor($hours / 24);
		$hours = $hours % 24;
	}
	if ($days >= 7) {
		$weeks = floor($days / 7);
		$days = $days % 7;
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

	if (count($parts) == 1) {
		$result = $parts[0];
	} else {
		// e.g. 5h, 10m and 30s
		$result = implode(', ', array_slice($parts, 0, -1)) . ' and ' . end($parts);
	}

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
 */
function getWeightedRandom(array $choices): string|int {
	// Normalize the weights so that their sum is much larger than 1.
	$maxWeight = max($choices);
	foreach ($choices as $key => $weight) {
		$choices[$key] = IRound($weight * 1000 / $maxWeight);
	}

	// Generate a random number that is lower than the sum of the weights.
	$rand = rand(1, array_sum($choices));

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
