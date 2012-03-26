<?php

require_once(LIB . '/Default/Globals.class.inc');
require_once(get_file_loc('SmrPlayer.class.inc'));
require_once(get_file_loc('SmrAccount.class.inc'));
require_once(get_file_loc('SmrShip.class.inc'));
require_once(get_file_loc('SmrSector.class.inc'));
require_once(get_file_loc('Sorter.class.inc'));

function htmliseMessage($message) {
	$message = htmlentities($message,ENT_COMPAT,'utf-8');
	$message = str_replace('&lt;br /&gt;','<br />',$message);
	return $message;
}

function parseBoolean($check) {
	if(is_string($check) && (strcasecmp($check,'NO') == 0 || strcasecmp($check,'FALSE')==0))
		return true;
	return (bool)$tagParams['showalliance'];
}

function smrBBCode($bbParser, $action, $tagName, $default, $tagParams, $tagContent) {
	global $overrideGameID, $disableBBLinks, $player, $account;
	try {
		switch($tagName) {
			case 'player':
				$playerID = $default;
				if(!is_numeric($playerID))
					$playerID = $tagParams['id'];
				$bbPlayer =& SmrPlayer::getPlayerByPlayerID($playerID, $overrideGameID);
				if ($action == BBCODE_CHECK) {
					return true;
				}
				if($disableBBLinks===false && $overrideGameID==SmrSession::$game_id) {
					return $bbPlayer->getLinkedDisplayName($showAlliance);
				}
				return $bbPlayer->getDisplayName(parseBoolean($tagParams['showalliance']));
			break;
			case 'alliance':
				$db = new SmrMySqlDatabase();
				$allianceID = $default;
				if(!is_numeric($allianceID))
					$allianceID = $tagParams['id'];
				$alliance =& SmrAlliance::getAlliance($allianceID, $overrideGameID);
				if ($action == BBCODE_CHECK) {
					return true;
				}

				if($disableBBLinks===false && $overrideGameID==SmrSession::$game_id) {
					$container = create_container('skeleton.php');
					$container['alliance_id'] = $alliance->getAllianceID();
					if (is_object($player) && $alliance->getAllianceID() == $player->getAllianceID())
						$container['body'] = 'alliance_mod.php';
					else
						$container['body'] = 'alliance_roster.php';
					return create_link($container, $alliance->getAllianceName());
				}
				return $alliance->getAllianceName();
			break;
			case 'race':
				$raceNameID = $default;
				$RACES =& Globals::getRaces();
				foreach($RACES as $raceID => $raceInfo) {
					if((is_numeric($raceNameID) && $raceNameID == $raceID)
						|| $raceNameID == $raceInfo['Race Name']) {
						if ($action == BBCODE_CHECK) {
							return true;
						}

						if($disableBBLinks===false && $overrideGameID==SmrSession::$game_id) {
							$container = create_container('skeleton.php','council_list.php', array('race_id' => $raceID));
							return create_link($container,  AbstractSmrPlayer::getColouredRaceNameOrDefault($raceID,$player));
						}
						return AbstractSmrPlayer::getColouredRaceNameOrDefault($raceID,$player);
					}
				}
			break;
			case 'servertimetouser':
				$timeString = $default;
				if ($action == BBCODE_CHECK) {
					return true;
				}
				if ($timeString != '' && ($time = strtotime($timeString)) !== false) {
					if(is_object($account))
						$time += $account->getOffset() * 3600;
					return date(DATE_FULL_SHORT, $time);
				}
			break;
			case 'chess':
				$chessGameID = $default;
				require_once(get_file_loc('ChessGame.class.inc'));
				$chessGame =& ChessGame::getChessGame($chessGameID);
				if ($action == BBCODE_CHECK) {
					return true;
				}
				return '<a href="' . $chessGame->getPlayGameHREF() . '">chess game (' . $chessGame->getChessGameID() . ')</a>';
			break;
		}
	}
	catch (Exception $e) {
	}
	if ($action == BBCODE_CHECK) {
		return false;
	}
	return htmlspecialchars($params['_tag']) . $content . htmlspecialchars($params['_endtag']);
}

function &xmlify(&$str) {
	$xml = str_replace('&nbsp;','&#160;',htmlentities($str,ENT_COMPAT,'utf-8'));
	return $xml;
}

function &bbifyMessage($message,$noLinks=false) {
	static $bbParser;
	if(!isset($bbParser)) {
		require_once(LIB.'External/BBCode/nbbc.php');
		$bbParser = new BBCode();
		$bbParser->SetEnableSmileys(false);
		$bbParser->RemoveRule('wiki');
		$bbParser->RemoveRule('img');
		$bbParser->SetURLTarget('_blank');
		$bbParser->SetURLTargetable('override');
		$smrRule = array(
				'mode' => BBCODE_MODE_CALLBACK,
				'method' => 'smrBBCode',
				'class' => 'link',
				'allow_in' => Array('listitem', 'block', 'columns', 'inline'),
				'end_tag' => BBCODE_PROHIBIT,
				'content' => BBCODE_PROHIBIT,
			);
		$bbParser->AddRule('player',$smrRule);
		$bbParser->AddRule('alliance',$smrRule);
		$bbParser->AddRule('race',$smrRule);
		$bbParser->AddRule('servertimetouser',$smrRule);
		$bbParser->AddRule('chess',$smrRule);
	}
	global $disableBBLinks;
	if($noLinks===true)
		$disableBBLinks = true;
	else
		$disableBBLinks = false;
	if(strpos($message, '[')!==false) { //We have BBCode so let's do a full parse.
		$message = $bbParser->parse($message);
		$message = str_replace('&lt;br /&gt;','<br />',$message);
	}
	else { //Otherwise just convert newlines
		$message = nl2br($message, true);
	}
	return $message;
}

function enableProtectionDependantRefresh(&$template,&$player) {
	$ajaxRefresh = AJAX_UNPROTECTED_REFRESH_TIME;
	if ($player->hasNewbieTurns()||$player->isLandedOnPlanet()||$player->hasFederalProtection())
		$ajaxRefresh=AJAX_DEFAULT_REFRESH_TIME;
	$template->assign('AJAX_ENABLE_REFRESH',$ajaxRefresh);
}

function hardware_is_mining_drone($hardwareTypeID) {
//	if ($hardware == 'Rundown Mining Drones' || $hardware == 'Simple Mining Drones' || $hardware == 'Basic Mining Drones' || $hardware == 'Advanced Mining Drones' || $hardware == 'Elite Mining Drones')
//		return TRUE;
//	else
		return FALSE;
}

function has_beta_privilege($priv = '', $acc = 0) {
	//PAGE
	return false;
	global $PRIVILEGES, $ACCOUNT_ID,$BETA_CONSTANTS,$USE_BETA_CONSTANTS;
	if($USE_BETA_CONSTANTS !== true)
		return false;
	if ($acc == 0) $acc = $ACCOUNT_ID;
	switch($priv) {
		case 'Create Universe':
			if(isset($BETA_CONSTANTS['Anyone Create Games']) && $BETA_CONSTANTS['Anyone Create Games']===true)
			return true;
	}

	if(isset($BETA_CONSTANTS[$priv])) {
		if ($BETA_CONSTANTS[$priv] === true) return true;
		if (is_array($BETA_CONSTANTS[$priv]) && in_array($acc, $BETA_CONSTANTS[$priv])) return true;
	}
	if (!isset($BETA_CONSTANTS['Privileges'][$acc])) return false; //means they have no privs
	if ($priv == '') return true; //means they have some kind of priv
	if(!isset($PRIVILEGES[$priv]))
		return false;
	if (in_array($PRIVILEGES[$priv], $BETA_CONSTANTS['Privileges'][$acc])) return true; //specific priv allowed
	return false;
}

function has_privilege($priv = '', $acc = 0) {
	//PAGE
	return false;
	//if ($priv == 'Always Join Alliances') return TRUE;
	//if ($priv == 'See Location') return TRUE;
	//if ($priv == 'Money Doesn\'t Matter') return TRUE;
	global $ADMINS, $PRIVILEGES, $ACCOUNT_ID;
	if ($acc == 0) $acc = $ACCOUNT_ID;
	if(has_beta_privilege($priv,$acc))
		return true;
	if (!isset($ADMINS[$acc])) return FALSE; //means they have no privs
	if ($priv == '') return TRUE; //means they have some kind of priv
	if (in_array($PRIVILEGES[$priv], $ADMINS[$acc])) return TRUE; //specific priv allowed
	return FALSE;
}

function create_echo_error($message) {

	$return=('<h1>ERROR</h1>');
	$return.=('<p><b><big>'.$message.'</big></b></p>');
	$return.=('<br /><br /><br />');
	$return.=('<p><small>If the error was caused by something you entered, press back and try again.</small></p>');
	$return.=('<p><small>If it was a DB Error, press back and try again, or logoff and log back on.</small></p>');
	$return.=('<p><small>If the error was unrecognizable, please notify the administrators.</small></p>');
}

function create_menu($menu) {
	global $template;
	$return = '<span class="noWrap">';
	foreach ($menu as $number => $entry) {
		if ($number > 0) $return .= '</span> | <span class="noWrap">';
		$return .= $entry;
	}
	$return .= '</span>';
	$template->unassign('MenuItems');
	$template->assign('MenuBar',$return);
}

function create_topic($topic) {
	return '<h1>' . strtoupper($topic) . '</h1><br />';
}

function create_table() {
 	return '<table class="standard">';
}

function create_echo_form($container) {
	return create_form_name($container, 'FORM');
}

function create_form_name($container, $name) {
	return create_form_parameter($container, 'name="'.$name.'"');
}

function create_form_parameter($container, $param) {
	return '<form '.$param.' method="POST" action="'.SmrSession::get_new_href($container).'">';
}

function create_header_link($container, $text) {
	return '<a class="header" href="' . SmrSession::get_new_href($container) . '">' . $text . '</a>';
}



function create_button($container,$text) {
	return '<div class="buttonA"><a class="buttonA" href="' . SmrSession::get_new_href($container). '">&nbsp;' . $text . '&nbsp;</a></div>';
}

function create_form($container,$actions) {
	static $form_id = 0;
	$form = array();
	$form['form'] = '<form class="standard" id="FORM' . $form_id . '" method="POST" action="' . SmrSession::get_new_href($container) . '">';

	if(!is_array($actions)) {
		$form['submit'] =  '<input class="submit" type="submit" name="action" value="' . htmlspecialchars($actions) . '">';
	}
	else {
		$form['submit'] = array();
		foreach($actions as $action) {
			$form['submit'][$action[0]] =  '<input class="submit" type="submit" name="action" value="' . htmlspecialchars($action[1]) . '">';
		}
	}

	++$form_id;
	return $form;

}

function create_submit($value) {
	return ('<input type="submit" name="action" value="'.htmlspecialchars($value).'" id="InputFields" />');
}

function create_submit_style($value, $style) {
	return ('<input type="submit" name="action" value="'.htmlspecialchars($value).'" id="InputFields" style="'.$style.'" />');
}

function create_error($message) {
	if(USING_AJAX)
		throw new Exception('No errors should be created when using AJAX as nothing should have changed from the last time the page loaded: ' . $message);
	$container = create_container('skeleton.php','error.php');
	$container['message'] = $message;
	forward($container);
}

function resetContainer($new_container) {
	global $time_start,$sn,$container,$var;

	// this sn identifies our container later

	SmrSession::resetLink($new_container,$sn);

	$var = $new_container;
	$container = $new_container;
}

function forward($new_container) {
	if(defined('OVERRIDE_FORWARD')&&OVERRIDE_FORWARD===true)
		return overrideForward($new_container);
	resetContainer($new_container);
	do_voodoo();
}

function forwardURL($new_container) {
	resetContainer($new_container);
	global $var;
	require_once(get_file_loc($var['url']));

}

function transfer($what) {
	global $var, $container;

	// transfer this value to next container
	if(isset($var[$what]))
		$container[$what] = $var[$what];

}

function create_container($file, $body = '', array $extra = array(), $remainingPageLoads = null) {
	$container = $extra;
	$container['url'] = $file;
	$container['body'] = $body;
	if($remainingPageLoads) {
		$container['RemainingPageLoads'] = $remainingPageLoads;
	}
	return $container;
}

function create_link($container, $text, $class=null) {
	return '<a'.($class==null?'':' class="'.$class.'"').' href="' . (is_array($container)?SmrSession::get_new_href($container):$container) . '">' . $text . '</a>';
}

function create_submit_link($container, $text) {
	return '<a href="' . SmrSession::get_new_href($container) . '" class="submitStyle">' . $text . '</a>';
}

function get_colored_text_range($value, $maxValue, $text = null, $minValue = 0, $type='Game', $return_type = 'Normal') {
	if($text == null) {
		$text = number_format($value);
	}
	if($maxValue-$minValue==0) {
		return $text;
	}
	else {
		$normalisedValue = round(510*max(0,min($maxValue,$value)-$minValue)/($maxValue-$minValue))-255;
	}
	if ($type == 'Game') {
		if($normalisedValue < 0) {
			$r_component='ff';
			$g_component = dechex(255+$normalisedValue);
			if(strlen($g_component) == 1) {
				$g_component = '0' . $g_component;
			}
		}
		else if($normalisedValue > 0) {
			$g_component='ff';
			$r_component = dechex(255-$normalisedValue);
			if(strlen($r_component) == 1) {
				$r_component = '0' . $r_component;
			}
		}
		else {
			$r_component = 'ff';
			$g_component = 'ff';
		}
		$colour = $r_component . $g_component . '00';
		if ($return_type == 'Color') return $colour;
		return '<span style="color:#' . $colour . '">' . $text . '</span>';
	}
	elseif ($type == 'IRC') {
		//IRC color codes
		if ($normalisedValue == 255) $colour = '[k03]';
		elseif ($normalisedValue == -255) $colour = '[k04]';
		else $colour = '[k08]';
		if ($return_type == 'Color') return $colour;
		return $colour . $text;
	}
}

function get_colored_text($value, $text = null, $type='Game', $return_type = 'Normal') {
	return get_colored_text_range($value, 300, $text, -300, $type, $return_type);
}

function makeBold($value) {
	if ($value)
		return ' style="font-weight:bold;"';
	else
		return '';
}

//function different_level($rank1, $rank2, $forced_vet1, $forced_vet2) {
//
//	return false;
//	// we are newbie, he vet
//	if ($rank1 < FLEDGLING && $rank2 > BEGINNER && $forced_vet1 == 'FALSE')
//		return true;
//
//	// we are vet, he newbie
//	if ($rank1 > BEGINNER && $rank2 < FLEDGLING && $forced_vet2 == 'FALSE')
//		return true;
//
//	// we are a forces vet, he newbie
//	if ($forced_vet1 == 'TRUE' && $rank2 < FLEDGLING && $forced_vet2 == 'FALSE')
//		return true;
//
//	// we newbie, he forced vet
//	if ($rank1 < FLEDGLING && $forced_vet2 == 'TRUE' && $forced_vet1 == 'FALSE')
//		return true;
//	return false;
//
//}

function hex2bin( $data ) {
	$len = strlen( $data );
	return pack( 'H' . $len, $data );
}

function word_filter($string) {
	static $words;

	if(!is_array($words)) {
		require_once(LIB . '/Default/SmrMySqlDatabase.class.inc');
		$db = new SmrMySqlDatabase();
		$db->query('SELECT word_value, word_replacement FROM word_filter');
		$words = array();
		while($db->nextRecord()) {
			$row = $db->getRow();
			$words[] = array('word_value' => '/'.str_replace('/','\/',$row['word_value']).'/i','word_replacement'=> $row['word_replacement']);
		}
	}

	foreach ($words as $word) {
		$string = preg_replace($word['word_value'],$word['word_replacement'],$string);
	}

	return $string;

}



// This function is a hack around the old style http forward mechanism
function do_voodoo() {
	global $lock, $var, $template, $account;
	if(!defined('AJAX_CONTAINER')) {
		define('AJAX_CONTAINER', isset($var['AJAX']) && $var['AJAX'] === true);
	}
	if(!AJAX_CONTAINER && USING_AJAX && SmrSession::$SN != SmrSession::$lastSN)
		exit;
//	ob_clean();

	foreach ($GLOBALS as $key => $value) {
		$$key = &$GLOBALS[$key];
	}
	// create account object
	$account =& SmrAccount::getAccount(SmrSession::$account_id);
	$GLOBALS['account'] =& $account;

	if(!defined('DATE_DATE_SHORT')) define('DATE_DATE_SHORT',$account->getShortDateFormat());
	if(!defined('DATE_TIME_SHORT')) define('DATE_TIME_SHORT',$account->getShortTimeFormat());
	if(!defined('DATE_FULL_SHORT')) define('DATE_FULL_SHORT',DATE_DATE_SHORT.' '.DATE_TIME_SHORT);
	if(!defined('DATE_FULL_SHORT_SPLIT')) define('DATE_FULL_SHORT_SPLIT',DATE_DATE_SHORT.'\<b\r /\>'.DATE_TIME_SHORT);


	$db = new SmrMySqlDatabase();
	$GLOBALS['db'] =& $db;

	if($var['url'] == 'game_play_preprocessing.php') { // Would rather not have these here but if we go through the initialisation based on game id when leaving a classic game it breaks.
		SmrSession::clearLinks();
		if (SmrSession::$game_id > 0)
			$account->log(LOG_TYPE_GAME_ENTERING, 'Player left game '.SmrSession::$game_id);

		// reset game id
		SmrSession::$game_id = 0;
		SmrSession::update();

		forward(create_container('skeleton.php', 'game_play.php', $var));
	}
	else if($var['url'] == 'logoff_preprocessing.php') {
		SmrSession::$game_id = 0;
		SmrSession::update();

		// try to get a real ip first
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			$curr_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else
			$curr_ip = $_SERVER['REMOTE_ADDR'];

		// log?
		$account->log(LOG_TYPE_LOGIN, 'logged off from '.$curr_ip);
		$container = create_container('skeleton.php', 'logoff.php');
		$container['logoff'] = 'yes';
		forward($container);
	}

	// initialize objects we usually need, like player, ship
	if (SmrSession::$game_id > 0) {
		if(SmrGame::getGame(SmrSession::$game_id)->getEndDate() < TIME) {
			forward(create_container('game_play_preprocessing.php', '', array('errorMsg' => 'The game has ended.')));
		}
		// We need to acquire locks BEFORE getting the player information
		// Otherwise we could be working on stale information
		$db->query('SELECT sector_id FROM player WHERE account_id=' . $db->escapeNumber(SmrSession::$account_id) . ' AND game_id=' . $db->escapeNumber(SmrSession::$game_id) . ' LIMIT 1');
		$db->nextRecord();
		$sector_id = $db->getInt('sector_id');

		global $locksFailed;
		if(!USING_AJAX //AJAX should never do anything that requires a lock.
//			&& !isset($var['url']) && ($var['body'] == 'current_sector.php' || $var['body'] == 'map_local.php') //Neither should CS or LM and they gets loaded a lot so should reduce lag issues with big groups.
			) {
			if(!$lock && !isset($locksFailed[$sector_id])) {
				if(!acquire_lock($sector_id)) {
					create_error('Failed to acquire sector lock');
				}
				//Refetch var info in case it changed between grabbing lock.
				SmrSession::fetchVarInfo();
				if (!($var = SmrSession::retrieveVar(SmrSession::$SN))) {
					$db->query('INSERT INTO debug VALUES (\'SPAM\',' . $db->escapeNumber(SmrSession::$account_id) . ',0,0)');
					create_error('Please do not spam click!');
				}
			}
		}

		// Now that they've acquire a lock we can move on
		$player	=& SmrPlayer::getPlayer(SmrSession::$account_id, SmrSession::$game_id);
		$GLOBALS['player'] =& $player;

		if($player->isDead() && $var['url'] != 'death_processing.php' && !isset($var['override_death'])) {
			forward(create_container('death_processing.php'));
		}
		enableProtectionDependantRefresh($template,$player);

		$ship	=& $player->getShip();
		$GLOBALS['ship'] =& $ship;

		$sector	=& $player->getSector();
		$GLOBALS['sector'] =& $sector;

		// update turns on that player
		$player->updateTurns();

		if (!$player->isDead() && $player->getNewbieTurns() <= NEWBIE_TURNS_WARNING_LIMIT &&
			$player->getNewbieWarning() &&
			$var['url'] != 'newbie_warning_processing.php')
			forward(create_container('newbie_warning_processing.php'));
	}

	if($var['url'] != 'skeleton.php') {
		require(get_file_loc($var['url']));
	}
	if($var['body']) {
		$PHP_OUTPUT = '';
		if($var['body']=='error.php'||$var['body']=='bar_main.php') { // infinite includes for error/bar pages
			include(get_file_loc($var['body']));
		}
		else {
			include_once(get_file_loc($var['body']));
		}

		if($PHP_OUTPUT!='')
			$template->assign('PHP_OUTPUT',$PHP_OUTPUT);
	}

	if (SmrSession::$game_id > 0) {
		$template->assign('UnderAttack', $ship->removeUnderAttack());
	}

	if($lock) { //Only save if we have the lock.
		SmrSector::saveSectors();
		SmrShip::saveShips();
		SmrPlayer::savePlayers();
		SmrForce::saveForces();
		SmrPort::savePorts();
		SmrPlanet::savePlanets();
		if(class_exists('WeightedRandom', false)) {
			WeightedRandom::saveWeightedRandoms();
		}
		//Update session here to make sure current page $var is up to date before releasing lock.
		SmrSession::update();
		release_lock();
	}

	//Nothing below this point should require the lock.

	$template->assign('TemplateBody',$var['body']);
	if (SmrSession::$game_id > 0) {
		$template->assignByRef('ThisSector',$sector);
		$template->assignByRef('ThisPlayer',$player);
		$template->assignByRef('ThisShip',$ship);
	}
	$template->assignByRef('ThisAccount',$account);
	if($account->getCssLink()!=null) {
		$template->assign('ExtraCSSLink',$account->getCssLink());
	}
	doSkeletionAssigns($template,$player,$ship,$sector,$db,$account);
	$time_elapsed = microtimeDiff(microtime(),MICRO_TIME);
	$template->assign('ScriptRuntime',number_format($time_elapsed,4));

	if(isset($var['DisableAjax']) && $var['DisableAjax']===true) {
		$template->assign('AJAX_ENABLE_REFRESH',false);
	}

	$template->display($var['url'], USING_AJAX);

	SmrSession::update();

	exit;
}

//xdebug_dump_function_profile(2);

// This is hackish, but without row level locking it's the best we can do
function acquire_lock($sector) {
	global $db, $lock, $locksFailed;

	if($lock)
		return true;

	// Insert ourselves into the queue.
	$db->query('INSERT INTO locks_queue (game_id,account_id,sector_id,timestamp) VALUES(' . $db->escapeNumber(SmrSession::$game_id) . ',' . $db->escapeNumber(SmrSession::$account_id) . ',' . $db->escapeNumber($sector) . ',' . $db->escapeNumber(TIME) . ')');
	$lock = $db->getInsertID();

	for($i=0;$i<250;++$i) {
		if(time() - TIME >= LOCK_DURATION - LOCK_BUFFER) {
			break;
		}
		// If there is someone else before us in the queue we sleep for a while
		$db->query('SELECT COUNT(*) FROM locks_queue WHERE lock_id<' . $db->escapeNumber($lock) . ' AND sector_id=' . $db->escapeNumber($sector) . ' AND game_id=' . $db->escapeNumber(SmrSession::$game_id) . ' AND timestamp > ' . $db->escapeNumber(TIME - LOCK_DURATION));
		$locksInQueue = -1;
		if($db->nextRecord() && ($locksInQueue = $db->getInt('COUNT(*)')) > 0) {
			//usleep(100000 + mt_rand(0,50000));

			// We can only have one lock in the queue, anything more means someone is screwing around
			$db->query('SELECT COUNT(*) FROM locks_queue WHERE account_id=' . $db->escapeNumber(SmrSession::$account_id) . ' AND sector_id=' . $db->escapeNumber($sector) . ' AND timestamp > ' . $db->escapeNumber(TIME - LOCK_DURATION));
			if($db->nextRecord() && $db->getInt('COUNT(*)') > 1) {
				release_lock();
				$locksFailed[$sector] = true;
				create_error('Multiple actions cannot be performed at the same time!');
				exit;
			}

			usleep(25000 * $locksInQueue);
			continue;
		}
		else {
			return true;
		}
	}

	release_lock();
	$locksFailed[$sector] = true;
	return false;
}

function release_lock() {
	global $db, $lock;
	if($lock) {
		$db->query('DELETE from locks_queue WHERE lock_id=' . $db->escapeNumber($lock) . ' OR timestamp<' . $db->escapeNumber(TIME - LOCK_DURATION));
	}

	$lock = false;
}

function doTickerAssigns(&$template,&$player,&$db) {
	//any ticker news?
	if($player->hasTickers()) {
		$ticker = array();
		$max = TIME - 60;
		if($player->hasTicker('NEWS')) {
			$text = '';
			//get recent news (5 mins)

			$db->query('SELECT time,news_message FROM news WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND time >= '.$max.' ORDER BY time DESC LIMIT 4');
			while($db->nextRecord()) {
				$ticker[] = array('Time' => date(DATE_FULL_SHORT, $db->getField('time')),
								'Message'=>$db->getField('news_message'));
			}
		}
		if ($player->hasTicker('SCOUT')) {
			$db->query('SELECT message_text,send_time FROM message
						WHERE account_id=' . $db->escapeNumber($player->getAccountID()) . '
						AND game_id=' . $db->escapeNumber($player->getGameID()) . '
						AND message_type_id=' . $db->escapeNumber(MSG_SCOUT) . '
						AND send_time>=' . $db->escapeNumber($max) . '
						AND sender_id NOT IN (SELECT account_id FROM player_has_ticker WHERE type='.$db->escapeString('BLOCK').' AND expires > ' . $db->escapeNumber(TIME) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ') AND reciever_delete = \'FALSE\'
						ORDER BY send_time DESC
						LIMIT 4');
			while($db->nextRecord()) {
				$ticker[] = array('Time' => date(DATE_FULL_SHORT, $db->getField('send_time')),
								'Message'=>$db->getField('message_text'));
			}
		}
		$template->assign('Ticker',$ticker);
	}
}

function doSkeletionAssigns(&$template,&$player,&$ship,&$sector,&$db,&$account) {
	$template->assign('CSSLink',URL.'/css/'.$account->getTemplate().'.css');
	$template->assign('CSSColourLink',URL.'/css/'.$account->getTemplate().'/'.$account->getColourScheme().'.css');

	$template->assign('FontSize',$account->getFontSize());
	$template->assign('timeDisplay',date(DATE_FULL_SHORT_SPLIT,TIME));
	if(!$account->isUseAJAX()) {
		$template->assign('AJAX_ENABLE_REFRESH',false);
	}

	$container = create_container('skeleton.php');


	if (SmrSession::$game_id > 0) {
		$template->assign('GameName',Globals::getGameName(SmrSession::$game_id));
		$template->assign('GameID',SmrSession::$game_id);

		if ($player->isLandedOnPlanet()) {
			$template->assign('PlanetMainLink',Globals::getPlanetMainHREF());
		}
		else {
			$template->assign('LocalMapLink',Globals::getLocalMapHREF());
		}

		$template->assign('PlotCourseLink',Globals::getPlotCourseHREF());

		$template->assign('TraderLink',Globals::getTraderStatusHREF());

		$template->assign('PoliticsLink',Globals::getPoliticsHREF());

		$container['body'] = 'combat_log_viewer.php';
		$template->assign('CombatLogsLink',SmrSession::get_new_href($container));

		$template->assign('PlanetLink',Globals::getTraderPlanetHREF());

		$container['body'] = 'forces_list.php';
		$template->assign('ForcesLink',SmrSession::get_new_href($container));

		$container['body'] = 'message_view.php';
		$template->assign('MessagesLink',SmrSession::get_new_href($container));

		$container['body'] = 'news_read_current.php';
		$template->assign('ReadNewsLink',SmrSession::get_new_href($container));

		$container['body'] = 'galactic_post_read.php';
		$template->assign('GalacticPostLink',SmrSession::get_new_href($container));

		$container['body'] = 'trader_search.php';
		$template->assign('SearchForTraderLink',SmrSession::get_new_href($container));

		$container['body'] = 'rankings_player_experience.php';
		$template->assign('RankingsLink',SmrSession::get_new_href($container));

		$container['body'] = 'hall_of_fame_new.php';
		$container['game_id'] = $player->getGameID();
		$template->assign('CurrentHallOfFameLink',SmrSession::get_new_href($container));
	}

	if (SmrSession::$account_id > 0 && empty($var['logoff'])) {
		$container = create_container('skeleton.php', 'hall_of_fame_new.php');
		$template->assign('HallOfFameLink',SmrSession::get_new_href($container));

		$template->assign('AccountID',SmrSession::$account_id);
		$template->assign('PlayGameLink',SmrSession::get_new_href(create_container('game_play_preprocessing.php')));

		$template->assign('LogoutLink',SmrSession::get_new_href(create_container('logoff_preprocessing.php')));
	}

	$container = create_container('skeleton.php', 'preferences.php');
	$template->assign('PreferencesLink',SmrSession::get_new_href($container));

	$container['body'] = 'album_edit.php';
	$template->assign('EditPhotoLink',SmrSession::get_new_href($container));

	$container['body'] = 'bug_report.php';
	$template->assign('ReportABugLink',SmrSession::get_new_href($container));

	$container['body'] = 'contact.php';
	$template->assign('ContactFormLink',SmrSession::get_new_href($container));

	if (SmrSession::$game_id > 0) {
		$container['body'] = 'chat_rules.php';
		$template->assign('IRCLink',SmrSession::get_new_href($container));
	}

	$container['body'] = 'donation.php';
	$template->assign('DonateLink',SmrSession::get_new_href($container));



	if (SmrSession::$game_id != 0) {
		$db->query('SELECT message_type_id,COUNT(*) FROM player_has_unread_messages WHERE account_id=' . $db->escapeNumber($player->getAccountID()) . ' AND game_id=' . $db->escapeNumber($player->getGameID()) . ' GROUP BY message_type_id');

		if($db->getNumRows()) {
			$messages = array();
			while($db->nextRecord()) {
				$messages[$db->getInt('message_type_id')] = $db->getInt('COUNT(*)');
			}

			$container = create_container('skeleton.php','message_view.php');

			if(isset($messages[MSG_GLOBAL])) {
				$container['folder_id'] = MSG_GLOBAL;
				$template->assign('MessageGlobalLink',SmrSession::get_new_href($container));
				$template->assign('MessageGlobalNum',$messages[MSG_GLOBAL]);
			}

			if(isset($messages[MSG_PLAYER])) {
				$container['folder_id'] = MSG_PLAYER;
				$template->assign('MessagePersonalLink',SmrSession::get_new_href($container));
				$template->assign('MessagePersonalNum',$messages[MSG_PLAYER]);
			}

			if(isset($messages[MSG_SCOUT])) {
				$container['folder_id'] = MSG_SCOUT;
				$template->assign('MessageScoutLink',SmrSession::get_new_href($container));
				$template->assign('MessageScoutNum',$messages[MSG_SCOUT]);
			}

			if(isset($messages[MSG_POLITICAL])) {
				$container['folder_id'] = MSG_POLITICAL;
				$template->assign('MessagePoliticalLink',SmrSession::get_new_href($container));
				$template->assign('MessagePoliticalNum',$messages[MSG_POLITICAL]);
			}

			if(isset($messages[MSG_ALLIANCE])) {
				$container['folder_id'] = MSG_ALLIANCE;
				$template->assign('MessageAllianceLink',SmrSession::get_new_href($container));
				$template->assign('MessageAllianceNum',$messages[MSG_ALLIANCE]);
			}

			if(isset($messages[MSG_ADMIN])) {
				$container['folder_id'] = MSG_ADMIN;
				$template->assign('MessageAdminLink',SmrSession::get_new_href($container));
				$template->assign('MessageAdminNum',$messages[MSG_ADMIN]);
			}

			if(isset($messages[MSG_CASINO])) {
				$container['folder_id'] = MSG_CASINO;
				$template->assign('MessageCasinoLink',SmrSession::get_new_href($container));
				$template->assign('MessageCasinoNum',$messages[MSG_CASINO]);
			}

			if(isset($messages[MSG_PLANET])) {
				$container = create_container('planet_msg_processing.php');
				$template->assign('MessagePlanetLink',SmrSession::get_new_href($container));
				$template->assign('MessagePlanetNum',$messages[MSG_PLANET]);
			}
		}

		$container = create_container('skeleton.php', 'trader_search_result.php');
		$container['player_id']	= $player->getPlayerID();
		$template->assign('PlayerNameLink',SmrSession::get_new_href($container));

		if (is_array(Globals::getHiddenPlayers())&&in_array($player->getAccountID(), Globals::getHiddenPlayers())) $template->assign('PlayerInvisible',true);

		// ******* Hardware *******
		$container = create_container('skeleton.php', 'configure_hardware.php');

		$template->assign('HardwareLink',SmrSession::get_new_href($container));

		// ******* Forces *******
		$template->assign('ForceDropLink',SmrSession::get_new_href(create_container('skeleton.php', 'forces_drop.php')));

		if ($ship->hasMines()) {
			$container = create_container('forces_drop_processing.php');
			$container['owner_id'] = $player->getAccountID();
			$container['drop_mines'] = 1;
			$template->assign('DropMineLink',SmrSession::get_new_href($container));
		}
		if ($ship->hasCDs()) {
			$container = create_container('forces_drop_processing.php');
			$container['owner_id'] = $player->getAccountID();
			$container['drop_combat_drones'] = 1;
			$template->assign('DropCDLink',SmrSession::get_new_href($container));
		}

		if ($ship->hasSDs()) {
			$container = create_container('forces_drop_processing.php');
			$container['owner_id'] = $player->getAccountID();
			$container['drop_scout_drones'] = 1;
			$template->assign('DropSDLink',SmrSession::get_new_href($container));
		}

		$template->assign('CargoJettisonLink',SmrSession::get_new_href(create_container('skeleton.php', 'cargo_dump.php')));

		$template->assign('WeaponReorderLink',SmrSession::get_new_href(create_container('skeleton.php', 'weapon_reorder.php')));

		$nextVoteTime = array();
		$db->query('SELECT link_id,timeout FROM vote_links WHERE account_id=' . $db->escapeNumber($player->getAccountID()) . ' ORDER BY link_id LIMIT 3');
		while($db->nextRecord()) {
			$nextVoteTime[$db->getInt('link_id')] = TIME_BETWEEN_VOTING - (TIME - $db->getField('timeout'));
		}
	}

	$container = create_container('vote_link.php');

	$voteLinks = array();
	$voteLinks[1] = array('default_img' => 'mpogd.png', 'star_img' => 'mpogd_vote.png', 'location' => 'http://www.mpogd.com/games/game.asp?ID=1145', 'name' => 'MPOGD');
	//OMGN no longer do voting - the link actually just redirects to archive site.
	///$voteLinks[2] = array('default_img' => 'omgn.png', 'star_img' => 'omgn_vote.png', 'location' => 'http://www.omgn.com/topgames/vote.php?Game_ID=30', 'name' => 'OMGN');
	// as long as TWG uses this stupid captcha which is very hard to figure out we will not support this site
	// nor will we give free turns for NOT voting
	//$voteLinks[3] = array('default_img' => 'twg.png', 'star_img' => 'twg_vote.png', 'location' => 'http://www.topwebgames.com/in.asp?id=136', 'name' => 'TWG');

	$minVoteTime = 86400;
	$voteSite = array();
	for($i=1;$i<2;$i++) {
		if(!isset($nextVoteTime[$i])) {
			$nextVoteTime[$i] = 0;
		}
		$minVoteTime = min($minVoteTime,$nextVoteTime[$i]);
		$voteSite[$i] = '<a href=';
		if(SmrSession::$game_id != 0 && $nextVoteTime[$i] <= 0) {
			$container['link_id'] = $i;
			$voteSite[$i] .= '\'javascript:voteSite("' . $voteLinks[$i]['location'] . '",';
			$voteSite[$i] .= '"' . SmrSession::get_new_href($container,true) . '")\'';
			$img = $voteLinks[$i]['star_img'];
		}
		else {
			$voteSite[$i] .= '"' . $voteLinks[$i]['location'] . '" target="_blank"';
			$img = $voteLinks[$i]['default_img'];
		}

		$voteSite[$i] .= '><img class="vote_site" src="'.URL.'/images/game_sites/' . $img . '" alt="' . $voteLinks[$i]['name'] . '"></a>';
	}
	$template->assign('VoteSites',$voteSite);
	$template->assign('TimeToNextVote',$minVoteTime);


	$db->query('SELECT * FROM version ORDER BY went_live DESC LIMIT 1');
	$version = '';
	if ($db->nextRecord()) {
		$version_id = $db->getField('version_id');
		$container = create_container('skeleton.php', 'changelog_view.php', array('version_id' => $version_id));
		$version = create_link($container, 'v' . $db->getField('major_version') . '.' . $db->getField('minor_version') . '.' . $db->getField('patch_level'));
	}

	$template->assign('Version',$version);
	$template->assign('CurrentYear',date('Y',TIME));
//	$launch = mktime(0,0,0,3,12,2008);
//	$now = TIME;
//	if ($launch - $now > 0)
//		echo '<br />SMR 1.5 Launch in ' . format_time($launch - $now, TRUE) . '</span>!';
}

function format_time($seconds, $short=false) {
	$minutes=0;
	$hours=0;
	$days=0;
	$weeks=0;
	$string = '';
	if ($seconds == 0) {
		$string = '0 seconds';
		if ($short) $string = '0s';
	}
	if ($seconds >= 60) {
		$minutes = floor($seconds/60);
		$seconds = $seconds % 60;
	}
	if ($minutes >= 60) {
		$hours = floor($minutes/60);
		$minutes = $minutes % 60;
	}
	if ($hours >= 24) {
		$days = floor($hours/24);
		$hours = $hours % 24;
	}
	if ($days >= 7) {
		$weeks = floor($days/7);
		$days = $days % 7;
	}
	if ($weeks > 0) {
		$string .= $weeks;
		if ($short) $string .= 'w';
		else {
			$string .= ' week';
			if ($weeks > 1) $string .= 's';
		}
	}
	if ($days > 0) {
		$before = $weeks;
		$after = $hours + $minutes + $seconds;
		if ($before > 0 && $after > 0) $string .= ', ';
		elseif ($before > 0 && $after == 0) $string .= ' and ';
		$string .= $days;
		if ($short) $string .= 'd';
		else {
			$string .= ' day';
			if ($days > 1) $string .= 's';
		}
	}
	if ($hours > 0) {
		$before = $weeks + $days;
		$after = $minutes + $seconds;
		if ($before > 0 && $after > 0) $string .= ', ';
		elseif ($before > 0 && $after == 0) $string .= ' and ';
		$string .= $hours;
		if ($short) $string .= 'h';
		else {
			$string .= ' hour';
			if ($hours > 1) $string .= 's';
		}
	}
	if ($minutes > 0) {
		$before = $weeks + $days + $hours;
		$after = $seconds;
		if ($before > 0 && $after > 0) $string .= ', ';
		elseif ($before > 0 && $after == 0) $string .= ' and ';
		$string .= $minutes;
		if ($short) $string .= 'm';
		else {
			$string .= ' minute';
			if ($minutes > 1) $string .= 's';
		}
	}
	if ($seconds > 0) {
		$before = $weeks + $days + $hours + $minutes;
		$after = 0;
		if ($before > 0 && $after > 0) $string .= ', ';
		elseif ($before > 0 && $after == 0) $string .= ' and ';
		$string .= $seconds;
		if ($short) $string .= 's';
		else {
			$string .= ' second';
			if ($seconds > 1) $string .= 's';
		}
	}
	return $string;
}

function number_colour_format($number,$justSign = false) {
	$formatted = '<span';
	if($number > 0)
		$formatted .= ' class="green">+';
	else if($number < 0)
		$formatted .= ' class="red">-';
	else
		$formatted .= '>';
	if($justSign===false) {
		$decimalPlaces = 0;
		if(($pos = strpos($number,'.'))!==false)
			$decimalPlaces = strlen(substr($number,$pos+1));
		$formatted .= number_format(abs($number),$decimalPlaces);
	}
	$formatted .= '</span>';
	return $formatted;
}
?>