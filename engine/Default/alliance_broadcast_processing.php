<?php
$message = htmlentities(trim($_POST['message']));
if (strlen(message) == 0)
	create_error('You have to enter a text to send!');

$db->query('SELECT account_id FROM player WHERE game_id=' . $player->getGameID() . 
			' AND alliance_id=' . $var['alliance_id']); //No limit in case they are over limit - ie NHA

while ($db->nextRecord()) {
	$player->sendMessage($db->getField('account_id'), MSG_ALLIANCE, $message,false);
}
$container=array();
$container['url'] = 'skeleton.php';
$container['body'] = 'alliance_roster.php';
$container['alliance_id'] = $var['alliance_id'];
forward($container);

?>