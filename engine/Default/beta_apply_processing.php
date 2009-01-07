<?php

$new_sub = 'Beta Application';
$message = 'Login: '.$login.EOL.EOL.'-----------'.EOL.EOL.
	 'Webboard Name: '.$webboard.EOL.EOL.'-----------'.EOL.EOL.
	 'IRC Nick: '.$ircnick.EOL.EOL.'-----------'.EOL.EOL.
	 'Account ID: '.$account_id.EOL.EOL.'-----------'.EOL.EOL.
	 'Start Time: '.$started.EOL.EOL.'-----------'.EOL.EOL.
	 'Reasons: '.$reasons.EOL.EOL.'-----------'.EOL.EOL.
	 'Time spent on beta: '.$time.EOL.EOL.'-----------'.EOL.EOL.
	 'Online time: '.$online;
//mail('beta@smrealms.de',
//	 $new_sub,
//	 $message,
//	 'From: '.$account->email);
$player->sendMessage(ACCOUNT_PAGE, MSG_PLAYER, nl2br($message));

$container = array();
$container['url'] = 'skeleton.php';
if (SmrSession::$game_id > 0) {

	if ($player->isLandedOnPlanet())
		$container['body'] = 'planet_main.php';
	else
		$container['body'] = 'current_sector.php';

} else
	$container['body'] = 'game_play.php';

forward($container);

?>