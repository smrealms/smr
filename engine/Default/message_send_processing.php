<?

$message = nl2br($_POST['message']);

if (empty($message))
	create_error('You have to enter a text to send!');

if (empty($var['receiver']))
{

	// send to all online player
	$allowed = TIME - 600;
	$db->query('SELECT active_session.account_id FROM active_session, player
			WHERE active_session.last_accessed >= ' . $allowed . ' AND
				  active_session.game_id = '.SmrSession::$game_id . ' AND
				  active_session.account_id = player.account_id AND
				  active_session.game_id = player.game_id AND 
				  player.ignore_globals = \'FALSE\'');
//	$db->query('SELECT * FROM player WHERE game_id = '.$player->getGameID().' AND last_cpl_action >= '.$allowed.' AND ignore_globals = \'FALSE\'');

	while ($db->nextRecord())
	{
		$player->sendMessage($db->getField('account_id'), $GLOBALMSG, $message);
	}

}
else
{
	$player->sendMessage($var['receiver'], $PLAYERMSG, $message);
}

// get rid of all old scout messages (>24h)
$old = TIME - 86400;
$db->query('DELETE FROM message WHERE send_time < '.$old.' AND message_type_id = 4');

$container = array();
$container['url'] = 'skeleton.php';
if ($player->isLandedOnPlanet())
	$container['body'] = 'planet_main.php';
else
	$container['body'] = 'current_sector.php';

forward($container);

?>