<?php
try {
error_reporting(0); // turn off error reporting for clasic

//xdebug_start_profiling();

ob_start();

$time_start = getmicrotime();

// ********************************
// *
// * I n c l u d e s   h e r e
// *
// ********************************


// config file
require_once("config.inc");
require_once(ENGINE . "1.2/smr.inc");

// overwrite database class to use our db
require_once(get_file_loc('SmrMySqlDatabase.class.inc'));

require_once(get_file_loc('SmrSession.class.inc'));
require_once(get_file_loc('Globals.class.inc'));
// do we have a session?
if (SmrSession::$old_account_id == 0 || SmrSession::$game_id == 0 || Globals::getGameType(SmrSession::$game_id) != '1.2') {
	header('Location: ' . URL . '/loader.php');
	exit;
}

//include function
$includes = new SmrMySqlDatabase();
require_once(get_file_loc('smr_account.inc'));
require_once(get_file_loc('smr_player.inc'));
require_once(get_file_loc('smr_ship.inc'));
require_once(get_file_loc('smr_sector.inc'));

// We want these to be already defined as globals
$player=null;
$ship=null;
$sector=null;
$container=null;
$var=null;
$lock=false;

// new db object
$db = new SmrMySqlDatabase();

// ********************************
// *
// * c h e c k   S e s s i o n
// *
// ********************************

//echo "<pre>";print_r($session);echo'</pre>';
//exit;

// ********************************
// *
// * Get Hidden Admins
// *
// ********************************

$db->query("SELECT account_id FROM hidden_players");
$HIDDEN_PLAYERS = array(0);
while ($db->next_record())
	$HIDDEN_PLAYERS[] = $db->f("account_id");

// ********************************
// *
// * A c c o u n t
// *
// ********************************

// create account object
$account = new SMR_ACCOUNT();

// get account from db
$account->get_by_id(SmrSession::$old_account_id);

// ********************************
// *
// * g e t   S e s s i o n
// *
// ********************************
$sn = $_REQUEST['sn'];

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

// update session
SmrSession::update();
	do_voodoo();
}
catch(Exception $e) {
	handleException($e);
}

// This function is a hack around the old style http forward mechanism
function do_voodoo() {
	ob_clean();

	global $lock, $var;

	foreach ($GLOBALS as $key => $value) {
	   	$$key = &$GLOBALS[$key];
	}

	// initialize objects we usually need, like player, ship
	if (SmrSession::$game_id > 0) {
		$db = new SmrMySqlDatabase();
		// We need to acquire locks BEFORE getting the player information
		// Otherwise we could be working on stale information
		$db->query('SELECT sector_id FROM player WHERE account_id=' . SmrSession::$old_account_id . ' AND game_id=' . SmrSession::$game_id . ' LIMIT 1');
		$db->next_record();
		$sector_id=$db->f('sector_id');

		if(!$lock && $var['body'] != 'error.php' && !isset($var['ForwardError'])) {
			if(!acquire_lock($sector_id)) {
				create_error("Failed to acquire sector lock");
			}
		}

		// Now that they've acquire a lock we can move on
		$player	= new SMR_PLAYER(SmrSession::$old_account_id, SmrSession::$game_id);

		if($player->dead == 'TRUE' && $var['body'] != 'death.php' && !isset($var['override_death'])) {
				$container = array();
				$container["url"] = "skeleton.php";
				$container["body"] = "death.php";
				forward($container);
		}

		$ship	= new SMR_SHIP(SmrSession::$old_account_id, SmrSession::$game_id);

		// update turns on that player
		$player->update_turns($ship->speed);

		if ($player->newbie_turns <= 20 &&
			$player->newbie_warning == "TRUE" &&
			$var["body"] != "newbie_warning.php")
			forward(create_container("skeleton.php", "newbie_warning.php"));

	}

	require(get_file_loc($var["url"]));

	SmrSession::update();

	if($lock) {
		release_lock($lock);
	}

	exit;
}

//xdebug_dump_function_profile(2);

// This is hackish, but without row level locking it's the best we can do
function acquire_lock($sector) {
	global $db, $lock;

	if($lock)
		return true;
	// Insert ourselves into the queue.
	$db->query('INSERT INTO locks_queue (game_id,account_id,sector_id,timestamp) VALUES(' . SmrSession::$game_id . ',' . SmrSession::$old_account_id . ',' . $sector . ',' . time() . ')');

	$lock = $db->insert_id();

	for($i=0;$i<200;++$i) {
		// If there is someone else before us in the queue we sleep for a while
		$db->query('SELECT COUNT(*) FROM locks_queue WHERE lock_id<' . $lock . ' AND sector_id=' . $sector . ' and game_id=' . SmrSession::$game_id . ' LIMIT 1');
		$db->next_record();
		if($db->f('COUNT(*)')){
			//usleep(100000 + mt_rand(0,50000));

			// We can only have one lock in the queue, anything more means someone is screwing around
			$db->query('SELECT COUNT(*) FROM locks_queue WHERE account_id=' . SmrSession::$old_account_id . ' AND sector_id=' . $sector . ' LIMIT 1');
			if($db->next_record()) {
				if($db->f('COUNT(*)') > 1) {
					create_error("Multiple actions cannot be performed at the same time!");
					$db->query('DELETE FROM locks_queue WHERE lock_id=' . $lock);
					exit;
				}
			}

			usleep(25000 * $db->f('COUNT(*)'));
			continue;
		}
		else {
			return true;
		}
	}

	release_lock($lock);
	return false;
}

function release_lock() {
	global $db, $lock;
	$db->query('DELETE from locks_queue WHERE lock_id=' . $lock . ' OR timestamp<' . (time() - 15));

	$lock=false;
}
function format_time($seconds, $short=FALSE) {
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


function getmicrotime() {

	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);

}
function printmicrotime($rt) {

	$max = sizeof($rt) - 1;
	for ($j = 0; $j < $max; $j++) {
		$step = $j + 1;
		$runtime = number_format($rt[$step] - $rt[$j], 8);
		print("Step $step executed in $runtime seconds<br />");
	}

}
?>