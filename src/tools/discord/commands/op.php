<?php declare(strict_types=1);

require_once(TOOLS . 'chat_helpers/channel_msg_op_info.php');
require_once(TOOLS . 'chat_helpers/channel_msg_op_list.php');
require_once(TOOLS . 'chat_helpers/channel_msg_op_turns.php');

$fn_op = function($message) {
	$link = new GameLink($message->channel, $message->author);
	if (!$link->valid) {
		return;
	}
	$player = $link->player;

	// print info about the next op
	$results = shared_channel_msg_op_info($player);
	$message->channel->sendMessage(join(EOL, $results));
};

$fn_op_list = function($message) {
	$link = new GameLink($message->channel, $message->author);
	if (!$link->valid) {
		return;
	}
	$player = $link->player;

	// print list of attendees
	$results = shared_channel_msg_op_list($player);
	$message->channel->sendMessage(join(EOL, $results));
};

$fn_op_turns = function($message) {
	$link = new GameLink($message->channel, $message->author);
	if (!$link->valid) {
		return;
	}
	$player = $link->player;

	// print list of attendees
	$results = shared_channel_msg_op_turns($player);
	$message->channel->sendMessage(join(EOL, $results));
};

$cmd_op = $discord->registerCommand('op', mysql_cleanup($fn_op), ['description' => 'Get information about the next scheduled op']);

$cmd_op->registerSubCommand('list', mysql_cleanup($fn_op_list), ['description' => 'Get the op attendee list']);

$cmd_op->registerSubCommand('turns', mysql_cleanup($fn_op_turns), ['description' => 'Get the turns of op attendees']);
