<?php

if (strlen($_POST['message']) == 0)
	create_error('You have to enter a text to send!');

$message = nl2br(format_string($_POST['message'], true));

$db->query('
	SELECT account_id FROM player WHERE game_id=' . $player->game_id . 
	' AND alliance_id=' . $var['alliance_id'] . ' LIMIT 30'
);

while ($db->next_record()) {
	$player->send_message($db->f('account_id'), $ALLIANCEMSG, $message);
}
$container=array();
$container['url'] = 'skeleton.php';
$container['body'] = 'alliance_roster.php';
$container['alliance_id'] = $var['alliance_id'];
forward($container);

?>