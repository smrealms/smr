<?

$steps = $_REQUEST['steps'];
$subject = $_REQUEST['subject'];
$error_msg = $_REQUEST['error_msg'];
$description = $_REQUEST['description'];
$new_sub = '[Bug] '.$subject;

$message = 'Login: '.$account->login.EOL.EOL.'-----------'.EOL.EOL.
	 'Account ID: '.$account->account_id.EOL.EOL.'-----------'.EOL.EOL.
	 'Description: '.$description.EOL.EOL.'-----------'.EOL.EOL.
	 'Steps to repeat: '.$steps.EOL.EOL.'-----------'.EOL.EOL.
	 'Error Message: '.$error_msg;
	 
//mail('bugs@smrealms.de',
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