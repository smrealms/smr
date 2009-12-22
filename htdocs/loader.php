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

header('Cache-Control: no-cache, must-revalidate');
//A date in the past
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

if(isset($_REQUEST['ajax'])&&$_REQUEST['ajax']==1)
	$ajax=true;
else
	$ajax=false;

define('USING_AJAX',$ajax);

//xdebug_start_profiling();

//ob_start();

try
{
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
	
	// overwrite database class to use our db
	require_once(LIB . 'Default/SmrMySqlDatabase.class.inc');
	require_once(LIB . 'Default/Globals.class.inc');

	
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
	{
		require_once(get_file_loc('smr.inc'));
		create_error('Your browser lost the SN. Try to reload the page!');
	}
	
	// do we have such a container object in the db?
	if (!($var = SmrSession::retrieveVar($sn)))
	{
		if(!USING_AJAX)
		{
			require_once(get_file_loc('smr.inc'));
			create_error('Please avoid using the back button!');
		}
		else
			exit;
	}
	
	
	//used for include if we need a spec game script outside of the game
	if (isset($var['game_id'])) $g_id = $var['game_id'];
	else if (isset($var['GameID'])) $g_id = $var['GameID'];
	else $g_id = 0;

	
	require_once(get_file_loc('SmrAccount.class.inc'));
	$account =& SmrAccount::getAccount(SmrSession::$account_id);
	// get reason for disabled user
	if(($reason = $account->is_disabled())!==false)
	{
		// save session (incase we forward)
		SmrSession::update();
		if ($reason == 'Invalid eMail')
		{
			header('Location: '.URL.'/email.php');
			exit;
		}
		else
		{
			header('Location: '.URL.'/disabled.php');
			exit;
		}
	}
	
	require_once(get_file_loc('smr.inc'));
	do_voodoo();
}
catch(Exception $e)
{
	global $account,$var,$player;
	$errorType = 'Error';
	$message='';
	$currMySQLError='';
	if(is_object($account))
	{
		$message .= 'Login: '.$account->login.EOL.EOL.'-----------'.EOL.EOL.
			'Account ID: '.$account->account_id.EOL.EOL.'-----------'.EOL.EOL.
			'E-Mail: '.$account->email.EOL.EOL.'-----------'.EOL.EOL;
	}
	$message .= 'Error Message: '.$e->getMessage().EOL.EOL.'-----------'.EOL.EOL;
	if($currMySQLError = mysql_error())
	{
		$errorType = 'Database Error';
		$message .= 'MySQL Error MSG: '.mysql_error().EOL.EOL.'-----------'.EOL.EOL;
	}
	$message .=	'Trace MSG: '.$e->getTraceAsString().EOL.EOL.'-----------'.EOL.EOL.
		'$var: '.var_export($var,true).EOL.EOL.'-----------'.EOL.EOL.
		'USING_AJAX: '.var_export(USING_AJAX,true);
	try
	{
		release_lock(); //Try to release lock so they can carry on normally
	}
	catch(Exception $ee)
	{
		$message .= EOL.EOL.'-----------'.EOL.EOL.
					'Releasing Lock Failed' .EOL.
					'Message: ' . $ee->getMessage() .EOL.EOL;
		if($currMySQLError!=mysql_error())
		{
			$message .= 'MySQL Error MSG: '.mysql_error().EOL.EOL;
		}
		$message .= 'Trace: ' . $ee->getTraceAsString();
	}
	try
	{
		if(is_object($player))
			$player->sendMessageToBox(BOX_BUGS_AUTO, $message);
		else if(is_object($account))
			$account->sendMessageToBox(BOX_BUGS_AUTO, $message);
		else
			mail('bugs@smrealms.de',
			 'Automatic Bug Report',
			 $message,
			 'From: bugs@smrealms.de');
	}
	catch(Exception $e)
	{
		mail('bugs@smrealms.de',
		 'Automatic Bug Report',
		 $message,
		 'From: bugs@smrealms.de');
	}
	if(!USING_AJAX)
		header('location: ' . URL . '/error.php?msg='.urlencode($errorType));
	exit;
}
?>
