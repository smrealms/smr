<?php

$receiver = $_REQUEST['receiver'];
$subject = $_REQUEST['subject'];
$msg = $_REQUEST['msg'];

mail($receiver,
	 $subject,
	 'Login:'.EOL.'------'.EOL.$account->login.EOL.EOL .
	 'Account ID:'.EOL.'-----------'.EOL.$account->account_id.EOL.EOL .
	 'Message:'.EOL.'------------'.EOL.$msg,
	 'From: '.$account->email);
	 
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