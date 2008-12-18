<?

$message = nl2br($db->escape_string($_POST['message'], true));

if (empty($message))
	create_error('You have to enter a text to send!');

// send to all council members
$db->query('SELECT * FROM player ' .
		   'WHERE game_id = '.$player->getGameID().' AND ' .
				 'race_id = '.$var['race_id'].' ' .
		   'ORDER by experience DESC ' .
		   'LIMIT 20');

while ($db->next_record()) {
	$player->sendMessage($db->f('account_id'), $POLITICALMSG, $message);
}

$container = array();
$container['url'] = 'skeleton.php';
if ($player->isLandedOnPlanet())
	$container['body'] = 'planet_main.php';
else
	$container['body'] = 'current_sector.php';

forward($container);

?>