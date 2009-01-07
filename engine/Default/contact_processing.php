<?

$receiver = $_REQUEST['receiver'];
$subject = $_REQUEST['subject'];
$msg = $_REQUEST['msg'];

mail($receiver,
	 $subject,
	 'Login:\n------'.EOL.$account->login.'\n'.EOL .
	 'Account ID:\n-----------'.EOL.$account->account_id.'\n'.EOL .
	 'Message:\n------------'.EOL.$msg,
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