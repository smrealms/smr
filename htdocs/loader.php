<?php
try
{
	require_once('config.inc');
	
	
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
	$overrideGameID = 0;
	if (isset($var['game_id']) && is_numeric($var['game_id'])) $overrideGameID = $var['game_id'];
	if ($overrideGameID == 0 && isset($var['GameID']) && is_numeric($var['GameID'])) $overrideGameID = $var['GameID'];
	if($overrideGameID == 0) $overrideGameID = SmrSession::$game_id;

	
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
	handleException($e);
}
?>
