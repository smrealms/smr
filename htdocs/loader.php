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

function getmicrotime() {

	list($usec, $sec) = explode(' ', microtime());
	return ((float)$usec + (float)$sec);

}
function echomicrotime($rt) {

	$max = sizeof($rt) - 1;
	for ($j = 0; $j < $max; $j++)
	{
		$step = $j + 1;
		$runtime = number_format($rt[$step] - $rt[$j], 8);
		$PHP_OUTPUT.=('Step '.$step.' executed in '.$runtime.' seconds<br />');
	}
	
}

// config file
require_once('config.inc');
require_once('config.php');
require_once(ENGINE . 'Old_School/smr.inc');

// overwrite database class to use our db
require_once(LIB . 'global/smr_db.inc');

require_once(get_file_loc('SmrAccount.class.inc'));
// require_once(LIB . 'global/smr_player.inc');
//PAGE
require_once(get_file_loc('SmrPlayer.class.inc'));
require_once(get_file_loc('SmrShip.class.inc'));
require_once(get_file_loc('SmrSector.class.inc'));

// We want these to be already defined as globals
$player=null;
$ship=null;
$sector=null;
$container=null;
$var=null;
$lock=false;

// new db object
$db = new SMR_DB();

// ********************************
// *
// * c h e c k   S e s s i o n
// *
// ********************************

//echo '<pre>';echo_r($session);echo'</pre>';
//exit;
// do we have a session?
if (SmrSession::$account_id == 0) {

	header('Location: '.$URL.'/login.php');
	exit;

}

// ********************************
// *
// * Get Hidden Admins
// *
// ********************************

$db->query('SELECT account_id FROM hidden_players');
$HIDDEN_PLAYERS = array(0);//stop errors
while ($db->next_record())
	$HIDDEN_PLAYERS[] = $db->f('account_id');

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
// check if we got a sn number with our url
if (empty($sn))
	create_error('Your browser lost the SN. Try to reload the page!');

// do we have such a container object in the db?
if (empty(SmrSession::$var[$sn]))
	create_error('Please avoid using the back button!');

// now get the container array for this sn object
$var = SmrSession::$var[$sn];

//used for include if we need a spec game script outside of the game
if (isset($var['game_id'])) $g_id = $var['game_id'];
else $g_id = 0;

// check if the last script had a start time
if (isset($var['time']))
	$time_start = $var['time'];

// reset session var if we don't have an error
// this makes the previous links (beside the current)
// unavailable for a reload
if (!empty($var['body']) && $var['body'] != 'error.php') {

	// empty session container
//BACKBUTTON	
	SmrSession::$var = array();

	// allow the user to reload current page
	SmrSession::$var[$sn] = $var;

}


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

	global $lock, $var;
	
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
		$db->next_record();
		$sector_id=$db->f('sector_id');

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

		$ship	=& SmrShip::getShip(SmrSession::$game_id,SmrSession::$account_id);
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

	if($lock)
	{
		release_lock($lock);
	}
	global $smarty,$sector,$player,$ship;
	var_dump($var);
	$smarty->assign('ThisSector',$sector);
	$smarty->assign('ThisPlayer',$player);
	$smarty->assign('ThisShip',$ship);
	$smarty->assign('TemplateBody',$var['body']);
	$smarty->display(get_template_loc($var['url']));
	exit;
}

//xdebug_dump_function_profile(2);

// This is hackish, but without row level locking it's the best we can do
function acquire_lock($sector) {
	global $db, $lock;

	// Insert ourselves into the queue.
	$db->query('INSERT INTO locks_queue (game_id,account_id,sector_id,timestamp) VALUES(' . SmrSession::$game_id . ',' . SmrSession::$account_id . ',' . $sector . ',' . time() . ')');
			
	$lock = $db->insert_id();

	for($i=0;$i<200;++$i) {
		// If there is someone else before us in the queue we sleep for a while
		$db->query('SELECT COUNT(*) FROM locks_queue WHERE lock_id<' . $lock . ' AND sector_id=' . $sector . ' and game_id=' . SmrSession::$game_id . ' LIMIT 1');
		$db->next_record();
		if($db->f('COUNT(*)')){
			//usleep(100000 + mt_rand(0,50000));

			// We can only have one lock in the queue, anything more means someone is screwing around
			$db->query('SELECT COUNT(*) FROM locks_queue WHERE account_id=' . SmrSession::$account_id . ' AND sector_id=' . $sector . ' LIMIT 1');
			if($db->next_record()) {
				if($db->f('COUNT(*)') > 1) {
					create_error('Multiple actions cannot be performed at the same time!');
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

?>
