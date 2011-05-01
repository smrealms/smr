<?php
function logException(Exception $e)
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
		'USING_AJAX: '.(defined('USING_AJAX')?var_export(USING_AJAX,true):'undefined');
		
	try
	{
		if(function_exists('release_lock'))
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
	echo $message;
	ob_flush();
	exit;
	try
	{
		if(is_object($player) && method_exists($player,'sendMessageToBox'))
			$player->sendMessageToBox(BOX_BUGS_AUTO, $message);
		else if(is_object($account) && method_exists($account,'sendMessageToBox'))
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
	return $errorType;
}

function handleException(Exception $e)
{
	$errorType = logException($e);
	if(!defined('USING_AJAX')||!USING_AJAX)
		header('location: ' . URL . '/error.php?msg='.urlencode($errorType));
	exit;
}

function dumpMemDiff($msg)
{
	static $memory;
	@ob_end_clean();
	var_dump($msg);
	var_dump(($memory2 = memory_get_usage()) - $memory);
	$memory = $memory2;
	ob_start();
}

xhprof_enable(XHPROF_FLAGS_MEMORY);

function endProfiling()
{
	// stop profiler
	$xhprof_data = xhprof_disable();
	
	
	if(memory_get_peak_usage() > 4000000)
	{
		//
		// Saving the XHProf run
		// using the default implementation of iXHProfRuns.
		//
		include_once '/usr/share/php5-xhprof/' . "/xhprof_lib/utils/xhprof_lib.php";
		include_once '/usr/share/php5-xhprof/' . "/xhprof_lib/utils/xhprof_runs.php";
		
		$xhprof_runs = new XHProfRuns_Default();
		
		// Save the run under a namespace "xhprof_foo".
		//
		// **NOTE**:
		// By default save_run() will automatically generate a unique
		// run id for you. [You can override that behavior by passing
		// a run id (optional arg) to the save_run() method instead.]
		//
		$run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_foo");
		
//		echo "---------------\n".
//		     "Assuming you have set up the http based UI for \n".
//		     "XHProf at some address, you can view run at \n".
//		     "http://localhost/xhprof_html/index.php?run=$run_id&source=xhprof_foo\n".
//		     "---------------\n";
//		ob_flush();
	}
}

require_once('config.php');

//	//num races
//	$NUM_RACES = 8;
//
//
//	$STATS = array(
//		'Experience Gain' => array( //in game
//			'ID' => 1,
//			'Description' => 'Gain more experience as a trader.',
//			'Type' => array(1)
//		),
//		'Accuracy' => array(
//			'ID' => 2,
//			'Description' => 'Hit enemy ships more often.',
//			'Type' => array(2)
//		),
//		'Maneuverability' => array(
//			'ID' => 3,
//			'Description' => 'Dodge incoming shots more often.',
//			'Type' => array(1)
//		),
//		'Mechanics' => array(
//			'ID' => 4,
//			'Description' => 'Gain more ship condition per hour.',
//			'Type' => array(1,2,4)
//		),
//		'Weapon Damage' => array(
//			'ID' => 5,
//			'Description' => 'Make your weapons hit for more damage against enemy ships.',
//			'Type' => array(2)
//		),/*
//		'Weapon Power' => array( //replace this with something else (lower ship condition loss to techs, mine avoidance)
//			'ID' => 6,
//			'Description' => 'Carry more deadly weapons.',
//			'Type' => array(3)
//		),
//		'Ship Size' => array(
//			'ID' => 7,
//			'Description' => 'Add anything from more cargo holds to more shields to more weapons to your ship.',
//			'Type' => array(1,4)
//		),*/
//		'Trade Profit' => array(
//			'ID' => 8,
//			'Description' => 'Gain more money when trading.',
//			'Type' => array(1)
//		),
//		'Relations' => array(
//			'ID' => 9,
//			'Description' => 'Increase your personal relations with different races, allowing weapons to be purchased and more ports to be traded at.',
//			'Type' => array(1)
//		),
//		'Sensors' => array(
//			'ID' => 10,
//			'Description' => 'Have a better chance of seeing enemy\'s while they are cloaked.',
//			'Type' => array(2)
//		),
//		'Stealth' => array(
//			'ID' => 11,
//			'Description' => 'Reduce your risk of being seen while cloaked, and avoid pinging scout drones.',
//			'Type' => array(1)
//		),
//		'Bombardment' => array(
//			'ID' => 12,
//			'Description' => 'Do extra damage to stationary targets such as planets and ports.',
//			'Type' => array(3)
//		),
//		'Cheaper Forces' => array(
//			'ID' => 13,
//			'Description' => 'Buy forces for a reduced price.',
//			'Type' => array(4)
//		),
//		'Bigger Stacks' => array(
//			'ID' => 14,
//			'Description' => 'Lay more forces per sector.',
//			'Type' => array(4)
//		),
//		'Planet Drone Resist' => array(
//			'ID' => 15,
//			'Description' => 'Take less damage from planetary drones.',
//			'Type' => array(3)
//		),
//		'Shield Regen' => array(
//			'ID' => 16,
//			'Description' => 'Regenerate Shields over time.',
//			'Type' => array(3)
//		),
//		'Planet Build' => array(
//			'ID' => 17,
//			'Description' => 'Build structures and produce goods faster on planets.',
//			'Type' => array(4)
//		)
//	);
?>