<?php declare(strict_types=1);

require_once(TOOLS . 'chat_helpers/channel_msg_op_info.php');
require_once(TOOLS . 'chat_helpers/channel_msg_op_list.php');
require_once(TOOLS . 'chat_helpers/channel_msg_op_turns.php');

$fn_op = function($message) {
	$link = new GameLink($message);
	if (!$link->valid) {
		return;
	}

	// print info about the next op
	$results = shared_channel_msg_op_info($link->player);
	$message->reply(join(EOL, $results))
		->done(null, 'logException');
};

$fn_op_list = function($message) {
	$link = new GameLink($message);
	if (!$link->valid) {
		return;
	}

	// print list of attendees
	$results = shared_channel_msg_op_list($link->player);
	$message->reply(join(EOL, $results))
		->done(null, 'logException');
};

$fn_op_turns = function($message) {
	$link = new GameLink($message);
	if (!$link->valid) {
		return;
	}

	// print list of attendees
	$results = shared_channel_msg_op_turns($link->player);
	$message->reply(join(EOL, $results))
		->done(null, 'logException');
};

$cmd_op = $discord->registerCommand('op', mysql_cleanup($fn_op), ['description' => 'Get information about the next scheduled op']);

$cmd_op->registerSubCommand('list', mysql_cleanup($fn_op_list), ['description' => 'Get the op attendee list']);

$cmd_op->registerSubCommand('turns', mysql_cleanup($fn_op_turns), ['description' => 'Get the turns of op attendees']);
