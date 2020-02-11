<?php declare(strict_types=1);

require_once(TOOLS . 'chat_helpers/channel_msg_forces.php');

$fn_forces = function($message, $params) {
	$link = new GameLink($message->channel, $message->author);
	if (!$link->valid) {
		return;
	}

	// print the next expiring forces
	$option = $params[0] ?? null;
	$results = shared_channel_msg_forces($link->player, $option);
	$message->channel->sendMessage(join(EOL, $results));
};

$discord->registerCommand('forces', mysql_cleanup($fn_forces),
	['description' => 'Print time until next expiring force. Arguments optional.',
	 'usage' => '[galaxy name | seedlist]',
	]);
