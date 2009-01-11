<?
if (get_magic_quotes_gpc())
{
    function stripslashes_array($array)
    {
        return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
    }

    $_COOKIE = stripslashes_array($_COOKIE);
    $_FILES = stripslashes_array($_FILES);
    $_GET = stripslashes_array($_GET);
    $_POST = stripslashes_array($_POST);
    $_REQUEST = stripslashes_array($_REQUEST);
}

//xdebug_start_profiling();

//ob_start();

$time_start = microtime(true);

// ********************************
// *
// * I n c l u d e s   h e r e
// *
// ********************************

// We want these to be already defined as globals
$player=null;
$ship=null;
$sector=null;
$container=null;
$var=null;
$lock=false;

// config file
require_once('config.inc');
require_once('config.php');
require_once(ENGINE . 'Default/smr.inc');

// overwrite database class to use our db
require_once(LIB . 'Default/SmrMySqlDatabase.class.inc');

require_once(get_file_loc('SmrAccount.class.inc'));
require_once(get_file_loc('SmrPlayer.class.inc'));
require_once(get_file_loc('SmrShip.class.inc'));
require_once(get_file_loc('SmrSector.class.inc'));


// new db object
$db = new SmrMySqlDatabase();

// ********************************
// *
// * c h e c k   S e s s i o n
// *
// ********************************

//echo '<pre>';echo_r($session);echo'</pre>';
//exit;
// do we have a session?
if (SmrSession::$account_id == 0)
{
	header('Location: '.URL.'/login.php');
	exit;

}

// ********************************
// *
// * Get Hidden Admins
// *
// ********************************

$db->query('SELECT account_id FROM hidden_players');
$HIDDEN_PLAYERS = array(0);//stop errors
while ($db->nextRecord())
	$HIDDEN_PLAYERS[] = $db->getField('account_id');

// ********************************
// *
// * A c c o u n t
// *
// ********************************

// create account object
$account =& SmrAccount::getAccount(SmrSession::$account_id);

// ********************************
// *
// * g e t   S e s s i o n
// *
// ********************************
$sn = $_REQUEST['sn'];

// now get the container array for this sn object
;
	
// check if we got a sn number with our url
if (empty($sn))
	create_error('Your browser lost the SN. Try to reload the page!');

// do we have such a container object in the db?
if (!($var = SmrSession::retrieveVar($sn)))
	create_error('Please avoid using the back button!');


//used for include if we need a spec game script outside of the game
if (isset($var['game_id'])) $g_id = $var['game_id'];
else $g_id = 0;

// check if the last script had a start time
if (isset($var['time']))
	$time_start = $var['time'];


// now deny reload for processing scripts
// it forwards the user to an error site
// or do the same if we already on that site
//if (empty($var['body']) ||
//	$var['body'] == 'error.php' && $var['message'] == 'Please click the button only once!') {
//
//	$container = array();
//	$container['url'] = 'skeleton.php';
//	$container['body'] = 'error.php';
//	$container['message'] = 'Please click the button only once!';
//
//	SmrSession::$var[$sn] = $container;
//
//}

// update session
SmrSession::update();

do_voodoo();

// This function is a hack around the old style http forward mechanism
function do_voodoo()
{
//	ob_clean();

	global $lock, $var,$smarty,$time_start,$db,$account;
	
	foreach ($GLOBALS as $key => $value)
	{
	   	$$key = &$GLOBALS[$key];
	}

	// initialize objects we usually need, like player, ship
	if (SmrSession::$game_id > 0)
	{

		// We need to acquire locks BEFORE getting the player information
		// Otherwise we could be working on stale information
		$db->query('SELECT sector_id FROM player WHERE account_id=' . SmrSession::$account_id . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 1');
		$db->nextRecord();
		$sector_id=$db->getField('sector_id');

		if(!$lock && (!isset($var['body']) || $var['body'] != 'error.php') && !isset($var['ForwardError']))
		{
			if(!acquire_lock($sector_id))
			{
				create_error('Failed to acquire sector lock');
			}
		}

		// Now that they've acquire a lock we can move on
		$player	=& SmrPlayer::getPlayer(SmrSession::$account_id, SmrSession::$game_id);
		$GLOBALS['player'] =& $player;

		if($player->isDead() && $var['body'] != 'death.php' && !isset($var['override_death']))
		{
				$container = array();
				$container['url'] = 'skeleton.php';
				$container['body'] = 'death.php';
				forward($container);
		}

		$ship	=& $player->getShip();
		$GLOBALS['ship'] =& $ship;
		
		$sector	=& SmrSector::getSector(SmrSession::$game_id,$player->getSectorID(),SmrSession::$account_id);
		$GLOBALS['sector'] =& $sector;

		// update turns on that player
		$player->updateTurns();

		if ($player->getNewbieTurns() <= NEWBIE_TURNS_WARNING_LIMIT &&
			$player->getNewbieWarning() &&
			$var['body'] != 'newbie_warning.php')
			forward(create_container('skeleton.php', 'newbie_warning.php'));

	}

	require_once(get_file_loc($var['url']));
	if($var['body'])
	{
		$PHP_OUTPUT = '';
		if($var['body']=='error.php') // infinite includes for error page
			include(get_file_loc($var['body']));
		else
			include_once(get_file_loc($var['body']));
			
		if($PHP_OUTPUT!='')
			$smarty->assign('PHP_OUTPUT',$PHP_OUTPUT);
	}
	
	$smarty->assign('TemplateBody',$var['body']);
	$time_elapsed = microtime(true) - $time_start;
	if (SmrSession::$game_id > 0)
	{
		$smarty->assign_by_ref('ThisSector',$sector);
		$smarty->assign_by_ref('ThisPlayer',$player);
		$smarty->assign_by_ref('ThisShip',$ship);
	}
	doSkeletionAssigns($smarty,$player,$ship,$sector,$db,$account);
	$smarty->assign('ScriptRuntime',number_format($time_elapsed,4));
	
	$smarty->display(get_template_loc($var['url']));
	
	if($lock)
	{ //only save if we have the lock.
		SmrShip::saveShips();
		SmrPlayer::savePlayers();
		SmrForce::saveForces();
		release_lock($lock);
	}
	SmrSession::update();
	exit;
}

//xdebug_dump_function_profile(2);

// This is hackish, but without row level locking it's the best we can do
function acquire_lock($sector)
{
	global $db, $lock;

	if($lock)
		return true;
		
	// Insert ourselves into the queue.
	$db->query('INSERT INTO locks_queue (game_id,account_id,sector_id,timestamp) VALUES(' . SmrSession::$game_id . ',' . SmrSession::$account_id . ',' . $sector . ',' . TIME . ')');
			
	$lock = $db->getInsertID();

	for($i=0;$i<100;++$i)
	{
		// If there is someone else before us in the queue we sleep for a while
		$db->query('SELECT COUNT(*) FROM locks_queue WHERE lock_id<' . $lock . ' AND sector_id=' . $sector . ' and game_id=' . SmrSession::$game_id . ' LIMIT 1');
		$db->nextRecord();
		if($db->getField('COUNT(*)'))
		{
			//usleep(100000 + mt_rand(0,50000));

			// We can only have one lock in the queue, anything more means someone is screwing around
			$db->query('SELECT COUNT(*) FROM locks_queue WHERE account_id=' . SmrSession::$account_id . ' AND sector_id=' . $sector . ' LIMIT 1');
			if($db->nextRecord())
			{
				if($db->getField('COUNT(*)') > 1)
				{
					$db->query('DELETE FROM locks_queue WHERE lock_id=' . $lock);
					create_error('Multiple actions cannot be performed at the same time!');
					exit;
				}
			}
			
			usleep(25000 * $db->getField('COUNT(*)'));
			continue;
		}
		else
		{
			return true;
		}
	}

	release_lock($lock);
	return false;
}

function release_lock()
{
	global $db, $lock;

	$db->query('DELETE from locks_queue WHERE lock_id=' . $lock . ' OR timestamp<' . (TIME - 15));

	$lock=false;
}

function doSkeletionAssigns(&$smarty,&$player,&$ship,&$sector,&$db,&$account)
{
	$smarty->assign('fontSize',$account->fontsize);
	$smarty->assign('timeDisplay',date('n/j/Y\<b\r /\>g:i:s A',TIME));
	
	$container = array();
	$container['url'] = 'skeleton.php';
	
	if (SmrSession::$game_id > 0) {
		$smarty->assign('GameID',SmrSession::$game_id);
	
		if ($player->isLandedOnPlanet())
		{
			$container['url'] = 'planet_main_processing.php';
			$container['body'] = '';
			$smarty->assign('PlanetMainLink',SmrSession::get_new_href($container));
		}
		else
		{
			$container['body'] = 'map_local.php';
			$smarty->assign('LocalMapLink',SmrSession::get_new_href($container));
		}
	
		$container['url'] = 'skeleton.php';
		$container['body'] = 'course_plot.php';
		$smarty->assign('PlotCourseLink',SmrSession::get_new_href($container));
		
		$container['url'] = 'skeleton.php';
		$container['body'] = 'trader_status.php';
		$smarty->assign('TraderLink',SmrSession::get_new_href($container));
	
		if ($player->getAllianceID() > 0)
		{
			$container['body'] = 'alliance_mod.php';
		}
		else
		{
			$container['body'] = 'alliance_list.php';
			$container['order'] = 'alliance_name';
		}
		$smarty->assign('AllianceLink',SmrSession::get_new_href($container));
	
		$container['body'] = 'combat_log_viewer.php';
		$smarty->assign('CombatLogsLink',SmrSession::get_new_href($container));
	
		$container['body'] = 'trader_planet.php';
		$smarty->assign('PlanetLink',SmrSession::get_new_href($container));
	
		$container['body'] = 'forces_list.php';
		$smarty->assign('ForcesLink',SmrSession::get_new_href($container));
	
		$container['body'] = 'message_view.php';
		$smarty->assign('MessagesLink',SmrSession::get_new_href($container));
	
		$container['body'] = 'news_read_current.php';
		$smarty->assign('ReadNewsLink',SmrSession::get_new_href($container));
	
		$container['body'] = 'galactic_post_read.php';
		$smarty->assign('GalacticPostLink',SmrSession::get_new_href($container));
	
		$container['body'] = 'trader_search.php';
		$smarty->assign('SearchForTraderLink',SmrSession::get_new_href($container));
	
		$container['body'] = 'current_players.php';
		$smarty->assign('CurrentPlayersLink',SmrSession::get_new_href($container));
	
		$container['body'] = 'rankings_player_experience.php';
		$smarty->assign('RankingsLink',SmrSession::get_new_href($container));
	
		$container['body'] = 'hall_of_fame_new.php';
		$smarty->assign('HallOfFameLink',SmrSession::get_new_href($container));
	
		$container['body'] = 'hall_of_fame_new.php';
		$container['game_id'] = $player->getGameID();
		$smarty->assign('CurrentHallOfFameLink',SmrSession::get_new_href($container));
		unset($container['game_id']);
	}
	
	if (SmrSession::$account_id > 0 && empty($var['logoff']))
	{
		$smarty->assign('AccountID',SmrSession::$account_id);
		$container['body'] = '';
		$container['url'] = 'game_play_preprocessing.php';
		$smarty->assign('PlayGameLink',SmrSession::get_new_href($container));
	
		$container['url'] = 'logoff_preprocessing.php';
		$smarty->assign('LogoutLink',SmrSession::get_new_href($container));
	}
	
	$container['url'] = 'skeleton.php';
	$container['body'] = 'preferences.php';
	$smarty->assign('PreferencesLink',SmrSession::get_new_href($container));
	
	/*
	$container['body'] = '';
	$container['url'] = 'mgu_create.php';
	$PHP_OUTPUT.=create_link($container, 'DL MGU Maps');
	echo '<br />';
	*/
	//if (SmrSession::$game_id > 0) {
	//	$container['body'] = '';
	//	$container['url'] = 'mgu_create_new.php';
	//	$PHP_OUTPUT.=create_link($container, 'DL MGU Maps');
	//	echo '<br />';
	//}
	$container['url'] = 'skeleton.php';
	$container['body'] = 'album_edit.php';
	$smarty->assign('EditPhotoLink',SmrSession::get_new_href($container));
	
	$container['body'] = 'bug_report.php';
	$smarty->assign('ReportABugLink',SmrSession::get_new_href($container));
	
	$container['body'] = 'contact.php';
	$smarty->assign('ContactFormLink',SmrSession::get_new_href($container));
	
	if (SmrSession::$game_id > 0)
	{
		$container['body'] = 'chat_rules.php';
		$smarty->assign('IRCLink',SmrSession::get_new_href($container));
	}
	
	$container['body'] = 'donation.php';
	$smarty->assign('DonateLink',SmrSession::get_new_href($container));
	
	
	
	if (SmrSession::$game_id != 0)
	{
	//	if ($under_attack_shields || $under_attack_armor || $under_attack_drones) {
	//		echo '
	//			<div id="attack_warning" class="attack_warning"><nobr>You are under attack!</nobr></div>
	//			<script type="text/javascript">
	//			SetBlink();
	//			</script>
	//			';
	//		$ship->removeUnderAttack();
	//	}
	
		$db->query('SELECT message_type_id,COUNT(*) FROM player_has_unread_messages WHERE account_id=' . $player->getAccountID() . ' AND game_id=' . $player->getGameID() . ' GROUP BY message_type_id');
	
		if($db->getNumRows()) {
			$messages = array();
			while($db->nextRecord()) {
				$messages[$db->getField('message_type_id')] = $db->getField('COUNT(*)');
			}
	
			$container = array();
			$container['url'] = 'skeleton.php';
			$container['body'] = 'message_view.php';
	
			if(isset($messages[MSG_GLOBAL])) {
				$container['folder_id'] = MSG_GLOBAL;
				$smarty->assign('MessageGlobalLink',SmrSession::get_new_href($container));
				$smarty->assign('MessageGlobalNum',$messages[MSG_GLOBAL]);
			}
	
			if(isset($messages[MSG_PLAYER])) {
				$container['folder_id'] = MSG_PLAYER;
				$smarty->assign('MessagePersonalLink',SmrSession::get_new_href($container));
				$smarty->assign('MessagePersonalNum',$messages[MSG_PLAYER]);
			}
	
			if(isset($messages[MSG_SCOUT])) {
				$container['folder_id'] = MSG_SCOUT;
				$smarty->assign('MessageScoutLink',SmrSession::get_new_href($container));
				$smarty->assign('MessageScoutNum',$messages[MSG_SCOUT]);
			}
	
			if(isset($messages[MSG_POLITICAL])) {
				$container['folder_id'] = MSG_POLITICAL;
				$smarty->assign('MessagePoliticalLink',SmrSession::get_new_href($container));
				$smarty->assign('MessagePoliticalNum',$messages[MSG_POLITICAL]);
			}
	
			if(isset($messages[MSG_ALLIANCE])) {
				$container['folder_id'] = MSG_ALLIANCE;
				$smarty->assign('MessageAllianceLink',SmrSession::get_new_href($container));
				$smarty->assign('MessageAllianceNum',$messages[MSG_ALLIANCE]);
			}
	
			if(isset($messages[MSG_ADMIN])) {
				$container['folder_id'] = MSG_ADMIN;
				$smarty->assign('MessageAdminLink',SmrSession::get_new_href($container));
				$smarty->assign('MessageAdminNum',$messages[MSG_ADMIN]);
			}
	
			if(isset($messages[MSG_PLANET])) {
				$container = array();
				$container['url'] = 'planet_msg_processing.php';
				$smarty->assign('MessagePlanetLink',SmrSession::get_new_href($container));
				$smarty->assign('MessagePlanetNum',$messages[MSG_PLANET]);
			}
		}
	
		$container = array();
		$container['url']		= 'skeleton.php';
		$container['body']		= 'trader_search_result.php';
		$container['player_id']	= $player->getPlayerID();
		$smarty->assign('PlayerNameLink',SmrSession::get_new_href($container));
		
		global $HIDDEN_PLAYERS;
		if (in_array($player->getAccountID(), $HIDDEN_PLAYERS)) $smarty->assign('PlayerInvisible',true);
	
		if ($player->getNewbieTurns() > NEWBIE_TURNS_WARNING_LIMIT)
			$colour = 'green';
		else
			$colour = 'red';
		$smarty->assign('NewbieTurnsColour',$colour);
	
		$db->query('SELECT ship_name FROM ship_has_name WHERE game_id = '.$player->getGameID().' AND ' .
					'account_id = '.$player->getAccountID().' LIMIT 1');
		if ($db->nextRecord()) {
			//they have a name so we echo it
			$smarty->assign('PlayerShipCustomName',stripslashes($db->getField('ship_name')));
		}
	
		// ******* Hardware *******
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'configure_hardware.php';
	
		$smarty->assign('HardwareLink',SmrSession::get_new_href($container));
	
		// ******* Forces *******
		$smarty->assign('ForceDropLink',SmrSession::get_new_href(create_container('skeleton.php', 'forces_drop.php')));
	
		if ($ship->hasMines())
		{
	
			$container = array();
			$container['url'] = 'forces_drop_processing.php';
			$container['owner_id'] = $player->getAccountID();
			$container['drop_mines'] = 1;
			$smarty->assign('DropMineLink',SmrSession::get_new_href($container));
		}
		if ($ship->hasCDs()) 
		{
	
			$container = array();
			$container['url'] = 'forces_drop_processing.php';
			$container['owner_id'] = $player->getAccountID();
			$container['drop_combat_drones'] = 1;
			$smarty->assign('DropCDLink',SmrSession::get_new_href($container));
	
		}
	
		if ($ship->hasSDs())
		{
	
			$container = array();
			$container['url'] = 'forces_drop_processing.php';
			$container['owner_id'] = $player->getAccountID();
			$container['drop_scout_drones'] = 1;
			$smarty->assign('DropSDLink',SmrSession::get_new_href($container));
	
		}
	
		$smarty->assign('CargoJettisonLink',SmrSession::get_new_href(create_container('skeleton.php', 'cargo_dump.php')));
	
		$smarty->assign('WeaponReorderLink',SmrSession::get_new_href(create_container('skeleton.php', 'weapon_reorder.php')));
	
		
	}
	
	$container=array();
	$container['url'] = 'vote_link.php';
	
	$in_game = isset(SmrSession::$game_id) && SmrSession::$game_id>0;
	if($in_game)
	{
		$db->query('SELECT link_id,timeout FROM vote_links WHERE account_id=' . SmrSession::$account_id . ' ORDER BY link_id LIMIT 3');
		while($db->nextRecord())
		{
			if($db->getField('timeout') < TIME - 86400)
			{
				$turns_for_votes[$db->getField('link_id')] = 1;
			}
			else {
				$turns_for_votes[$db->getField('link_id')] = 0;
			}
		}
	}
	
	$vote_links = array();
	$vote_links[1] = array('default_img' => 'mpogd.gif', 'star_img' => 'mpogd_vote.gif', 'location' => 'http://www.mpogd.com/gotm/vote.asp', 'name' => 'MPOGD');
	$vote_links[2] = array('default_img' => 'omgn.jpg', 'star_img' => 'omgn_vote.jpg', 'location' => 'http://www.omgn.com/topgames/vote.php?Game_ID=30', 'name' => 'OMGN');
	$vote_links[3] = array('default_img' => 'twg.gif', 'star_img' => 'twg_vote.gif', 'location' => 'http://www.topwebgames.com/in.asp?id=136', 'name' => 'TWG');
	
	$voteSite = array();
	for($i=1;$i<4;$i++){
		$voteSite[$i] = '<a href=';
		if($in_game && (!isset($turns_for_votes[$i]) || ($turns_for_votes[$i] && rand(0,100) < 80))) {
	
			$container['link_id'] = $i;
			$voteSite[$i] .= '\'javascript:VoteSite("' . $vote_links[$i]['location'] . '",';
			$voteSite[$i] .= '"' . SmrSession::get_new_sn($container) . '")\'';
			$img = $vote_links[$i]['star_img'];
		}
		else {
			$voteSite[$i] .= '"' . $vote_links[$i]['location'] . '" target="_blank"';
			$img = $vote_links[$i]['default_img'];
		}
	
		$voteSite[$i] .= '><img class="vote_site" src="images/game_sites/' . $img . '" alt="' . $vote_links[$i]['name'] . '"></a>';
	}
	$smarty->assign('VoteSites',$voteSite);
	
	
	$db->query('SELECT * FROM version ORDER BY went_live DESC LIMIT 1');
	$version = '';
	if ($db->nextRecord()) {
	
		$version_id = $db->getField('version_id');
		$container = array('url' => 'skeleton.php', 'body' => 'changelog_view.php', 'version_id' => $version_id );
		$version = create_link($container, 'v' . $db->getField('major_version') . '.' . $db->getField('minor_version') . '.' . $db->getField('patch_level'));
	
	}
	
	$smarty->assign('Version',$version);
	$smarty->assign('CurrentYear',date('Y',TIME));
//	$launch = mktime(0,0,0,3,12,2008);
//	$now = TIME;
//	if ($launch - $now > 0)
//		echo '<br />SMR 1.5 Launch in ' . format_time($launch - $now, TRUE) . '</span>!';
}

function format_time($seconds, $short=FALSE)
{
	$minutes=0;
	$hours=0;
	$days=0;
	$weeks=0;
	$string = '';
	if ($seconds == 0)
	{
		$string = '0 seconds';
		if ($short) $string = '0s';
	}
	if ($seconds >= 60)
	{
		$minutes = floor($seconds/60);
		$seconds = $seconds % 60;
	}
	if ($minutes >= 60)
	{
		$hours = floor($minutes/60);
		$minutes = $minutes % 60;
	}
	if ($hours >= 24)
	{
		$days = floor($hours/24);
		$hours = $hours % 24;
	}
	if ($days >= 7)
	{
		$weeks = floor($days/7);
		$days = $days % 7;
	}
	if ($weeks > 0)
	{
		$string .= $weeks;
		if ($short) $string .= 'w';
		else
		{
			$string .= ' week';
			if ($weeks > 1) $string .= 's';
		}
	}
	if ($days > 0)
	{
		$before = $weeks;
		$after = $hours + $minutes + $seconds;
		if ($before > 0 && $after > 0) $string .= ', ';
		elseif ($before > 0 && $after == 0) $string .= ' and ';
		$string .= $days;
		if ($short) $string .= 'd';
		else
		{
			$string .= ' day';
			if ($days > 1) $string .= 's';
		}
	}
	if ($hours > 0)
	{
		$before = $weeks + $days;
		$after = $minutes + $seconds;
		if ($before > 0 && $after > 0) $string .= ', ';
		elseif ($before > 0 && $after == 0) $string .= ' and ';
		$string .= $hours;
		if ($short) $string .= 'h';
		else
		{
			$string .= ' hour';
			if ($hours > 1) $string .= 's';
		}
	}
	if ($minutes > 0)
	{
		$before = $weeks + $days + $hours;
		$after = $seconds;
		if ($before > 0 && $after > 0) $string .= ', ';
		elseif ($before > 0 && $after == 0) $string .= ' and ';
		$string .= $minutes;
		if ($short) $string .= 'm';
		else
		{
			$string .= ' minute';
			if ($minutes > 1) $string .= 's';
		}
	}
	if ($seconds > 0)
	{
		$before = $weeks + $days + $hours + $minutes;
		$after = 0;
		if ($before > 0 && $after > 0) $string .= ', ';
		elseif ($before > 0 && $after == 0) $string .= ' and ';
		$string .= $seconds;
		if ($short) $string .= 's';
		else
		{
			$string .= ' second';
			if ($seconds > 1) $string .= 's';
		}
	}
	return $string;
}
?>
