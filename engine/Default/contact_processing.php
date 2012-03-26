<?php

$receiver = $_REQUEST['receiver'];
$subject = $_REQUEST['subject'];
$msg = $_REQUEST['msg'];

mail($receiver,
	$subject,
	'Login:'.EOL.'------'.EOL.$account->getLogin().EOL.EOL .
	'Account ID:'.EOL.'-----------'.EOL.$account->getAccountID().EOL.EOL .
	'Message:'.EOL.'------------'.EOL.$msg,
	'From: '.$account->getEmail());

$container = array();
$container['url'] = 'skeleton.php';
if (SmrSession::$game_id > 0) {
	if ($player->isLandedOnPlanet()) {
		$container['body'] = 'planet_main.php';
	}
	else {
		$container['body'] = 'current_sector.php';
	}
}
else {
	$container['body'] = 'game_play.php';
}

forward($container);

?>