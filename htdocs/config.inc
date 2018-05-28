<?php
function logException(Throwable $e) {
	global $account,$var,$player;
	$errorType = 'Unexpected Game Error!';
	$message='';
	$currMySQLError='';

	if(is_object($account)) {
		$message .= 'Login: '.$account->getLogin().EOL.EOL.'-----------'.EOL.EOL.
			'Account ID: '.$account->getAccountID().EOL.EOL.'-----------'.EOL.EOL.
			'E-Mail: '.$account->getEmail().EOL.EOL.'-----------'.EOL.EOL;
	}
	$message .= 'Error Message: '. $e .EOL.EOL.'-----------'.EOL.EOL;
	require_once(LIB . '/Default/SmrMySqlDatabase.class.inc');
	$db = new SmrMySqlDatabase();
	if($currMySQLError = $db->getError()) {
		$errorType = 'Database Error';
		$message .= 'MySQL Error MSG: '.$db->getError().EOL.EOL.'-----------'.EOL.EOL;
	}
	$message .=
		'$var: '.var_export($var,true).EOL.EOL.'-----------'.EOL.EOL.
		'USING_AJAX: '.(defined('USING_AJAX')?var_export(USING_AJAX,true):'undefined');

	try {
		if(function_exists('release_lock'))
			release_lock(); //Try to release lock so they can carry on normally
	}
	catch(Throwable $ee) {
		$message .= EOL.EOL.'-----------'.EOL.EOL.
					'Releasing Lock Failed' .EOL.
					'Message: ' . $ee .EOL.EOL;
		if($currMySQLError!=$db->getError()) {
			$message .= 'MySQL Error MSG: '.$db->getError().EOL.EOL;
		}
	}

	if (defined('NPC_SCRIPT')) {
		$message = 'Script: '.SCRIPT_ID.EOL.EOL.'-----------'.EOL.EOL.$message;
		var_dump($message);
		return;
	}

	if(ENABLE_DEBUG) {
		echo nl2br($message);
		exit;
	}

	// Send error message to the in-game auto bugs mailbox
	if (is_object($player) && method_exists($player,'sendMessageToBox')) {
		$player->sendMessageToBox(BOX_BUGS_AUTO, $message);
	} elseif (is_object($account) && method_exists($account,'sendMessageToBox')) {
		// Will be logged without a game_id
		$account->sendMessageToBox(BOX_BUGS_AUTO, $message);
	} else {
		// Will be logged without a game_id or sender_id
		SmrAccount::doMessageSendingToBox(0, BOX_BUGS_AUTO, $message, 0);
	}

	// Send error message to e-mail so that we have a permanent record
	if (!empty(BUG_REPORT_TO_ADDRESSES)) {
		$mail = setupMailer();
		$mail->Subject = 'Automatic Bug Report';
		$mail->setFrom('bugs@smrealms.de');
		$mail->Body = $message;
		foreach (BUG_REPORT_TO_ADDRESSES as $toAddress) {
			$mail->addAddress($toAddress);
		}
		$mail->send();
	}

	return $errorType;
}

function handleException(Throwable $e) {
	// The real error message may display sensitive information, so we
	// need to catch any exceptions that are thrown while logging the error.
	try {
		$errorType = logException($e);
	} catch (Throwable $e) {
		$errorType = 'This error cannot be automatically reported. Please notify an admin!';
	}

	// If this is an ajax update, we don't really have a way to redirect
	// to an error page at this time, so we just quit.
	if(!defined('USING_AJAX')||!USING_AJAX)
		header('location: ' . URL . '/error.php?msg='.urlencode($errorType));
	exit;
}

function dumpMemDiff($msg) {
	static $memory;
	@ob_end_clean();
	var_dump($msg);
	var_dump(($memory2 = memory_get_usage()) - $memory);
	$memory = $memory2;
	ob_start();
}

function microtimeDiff($m1,$m2) {
	$t1 = explode(' ', $m1);
	$t2 = explode(' ', $m2);
	return $t1[1] + $t1[0] - ($t2[1] + $t2[0]);
}

function microtimeSec($microtime) {
	if (strlen($microtime) == 21) {
		return substr($microtime,11);
	} else {
		throw new Exception('Not a microtime! ('.$microtime.')');
	}
}

function microtimeMSec($microtime) {
	if (strlen($microtime) == 21) {
		// Do not cast to int, because the substring can begin with '0'
		return substr($microtime,2,6);
	} else {
		throw new Exception('Not a microtime! ('.$microtime.')');
	}
}

function explodeElement($delimiter, $string, $index) {
	$arr = explode($delimiter, $string, $index+1);
	return $arr[$index];
}

require_once(__DIR__ . '/../vendor/autoload.php');
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
