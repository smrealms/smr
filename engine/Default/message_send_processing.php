<?

$message = nl2br($_POST['message']);

if (empty($message))
	create_error('You have to enter a text to send!');

if (empty($var['receiver']))
{
	$player->sendGlobalMessage($message);
}
else
{
	$player->sendMessage($var['receiver'], MSG_PLAYER, $message);
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