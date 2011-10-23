<?php
if(empty($_REQUEST['login'])||empty($_REQUEST['webboard'])||empty($_REQUEST['ircnick'])||empty($_REQUEST['started'])||empty($_REQUEST['reasons'])||empty($_REQUEST['time'])||empty($_REQUEST['online']))
{
	create_error('You must fill in all the fields!');
}
$new_sub = 'Beta Application';
$message = 'Login: '.$_REQUEST['login'].EOL.EOL.'-----------'.EOL.EOL.
	'Account ID: '.$account->getAccountID().EOL.EOL.'-----------'.EOL.EOL.
	'E-Mail: '.$account->getEmail().EOL.EOL.'-----------'.EOL.EOL.
	'Webboard Name: '.$_REQUEST['webboard'].EOL.EOL.'-----------'.EOL.EOL.
	'IRC Nick: '.$_REQUEST['ircnick'].EOL.EOL.'-----------'.EOL.EOL.
	'Start Time: '.$_REQUEST['started'].EOL.EOL.'-----------'.EOL.EOL.
	'Reasons: '.$_REQUEST['reasons'].EOL.EOL.'-----------'.EOL.EOL.
	'Time spent on beta: '.$_REQUEST['time'].EOL.EOL.'-----------'.EOL.EOL.
	'Online time: '.$_REQUEST['online'];
//mail('beta@smrealms.de',
//	 $new_sub,
//	 $message,
//	 'From: '.$account->getEmail());
$player->sendMessageToBox(BOX_BETA_APPLICATIONS, $message);

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