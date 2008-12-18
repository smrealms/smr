<?

$message = nl2br($db->escape_string($_POST['message'], true));

if (empty($message))
	create_error('You have to enter a text to send!');

if (empty($var['receiver'])) {

	// send to all online player
	$allowed = time() - 600;
	$db->query('SELECT * FROM player WHERE game_id = '.$player->getGameID().' AND last_cpl_action >= '.$allowed.' AND ignore_globals = \'FALSE\'');

	while ($db->next_record()) {
		$player->sendMessage($db->f('account_id'), $GLOBALMSG, $message);
	}

} else {
	$player->sendMessage($var['receiver'], $PLAYERMSG, $message);
}

// get rid of all old scout messages (>24h)
$old = time() - 86400;
$db->query('DELETE FROM message WHERE send_time < '.$old.' AND message_type_id = 4');

$container = array();
$container['url'] = 'skeleton.php';
if ($player->isLandedOnPlanet())
	$container['body'] = 'planet_main.php';
else
	$container['body'] = 'current_sector.php';

forward($container);

?>