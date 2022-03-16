<?php declare(strict_types=1);

require_once(TOOLS . 'chat_helpers/channel_msg_seed.php');

$fn_seed = function($message) {
	$link = new GameLink($message);
	if (!$link->valid) {
		return;
	}

	$result = shared_channel_msg_seed($link->player);
	$message->reply(implode(EOL, $result))
		->done(null, 'logException');
};

$discord->registerCommand('seed', mysql_cleanup($fn_seed), ['description' => 'List sectors with missing seeds']);
