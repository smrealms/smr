<?

$steps = $_REQUEST['steps'];
$subject = $_REQUEST['subject'];
$error_msg = $_REQUEST['error_msg'];
$login = $_REQUEST['login'];
$account_id = $_REQUEST['account_id'];
$description = $_REQUEST['description'];
$new_sub = '[Bug] '.$subject;
mail('bugs@smrealms.de',
	 $new_sub,
	 'Login:\n------'.EOL.$login.'\n'.EOL .
	 'Account ID:\n-----------'.EOL.$account_id.'\n'.EOL .
	 'Description:\n------------'.EOL.$description.'\n'.EOL .
	 'Steps to repeat:\n----------------'.EOL.$steps.'\n'.EOL .
	 'Error Message:\n--------------'.EOL.$error_msg,
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