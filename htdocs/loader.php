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

?>
