<?php

$message = htmlentities(trim($_POST['message']),ENT_COMPAT,'utf-8');

if (empty($message))
	create_error('You have to enter a text to send!');

// send to all council members
$db->query('SELECT * FROM player ' .
		   'WHERE game_id = '.$player->getGameID().' AND ' .
				 'race_id = '.$var['race_id'].' ' .
		   'ORDER by experience DESC ' .
		   'LIMIT ' . MAX_COUNCIL_MEMBERS);

while ($db->nextRecord()) {
	$player->sendMessage($db->getField('account_id'), MSG_POLITICAL, $message);
}

$container = array();
$container['url'] = 'skeleton.php';
if ($player->isLandedOnPlanet())
	$container['body'] = 'planet_main.php';
else
	$container['body'] = 'current_sector.php';

forward($container);

?>