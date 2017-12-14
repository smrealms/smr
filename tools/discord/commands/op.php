<?php

require_once(TOOLS . 'chat_helpers/channel_msg_op_info.php');

$fn_op = function ($message) {
	$link = new GameLink($message->channel, $message->author);
	if (!$link->valid) return;
	$player = $link->player;

	// print info about the next op
	$results = shared_channel_msg_op_info($player);
	$message->channel->sendMessage(join(EOL, $results));
};

$cmd_op = $discord->registerCommand('op', mysql_cleanup($fn_op), ['description' => 'Get information about the next scheduled op']);

?>
