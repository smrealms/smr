<?php

$new_sub = 'Beta Application';
mail('beta@smrealms.de',
	 $new_sub,
	 'Login:\n------'.EOL.$login.'\n'.EOL .
	 'Webboard Name:\n------'.EOL.$webboard.'\n'.EOL .
	 'IRC Nick:\n------'.EOL.$ircnick.'\n'.EOL .
	 'Account ID:\n-----------'.EOL.$account_id.'\n'.EOL .
	 'Start Time:\n-----------'.EOL.$started.'\n'.EOL .
	 'Reasons:\n------------'.EOL.$reasons.'\n'.EOL .
	 'Time spent on beta:\n----------------'.EOL.$time.'\n'.EOL .
	 'Online time:\n--------------'.EOL.$online,
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