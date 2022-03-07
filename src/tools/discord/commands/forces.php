<?php declare(strict_types=1);

require_once(TOOLS . 'chat_helpers/channel_msg_forces.php');

$fn_forces = function($message, $params) {
	$link = new GameLink($message);
	if (!$link->valid) {
		return;
	}

	// print the next expiring forces
	$option = $params[0] ?? null;
	$results = shared_channel_msg_forces($link->player, $option);
	$message->reply(implode(EOL, $results))
		->done(null, 'logException');
};

$discord->registerCommand('forces', mysql_cleanup($fn_forces),
	['description' => 'Print time until next expiring force. Arguments optional.',
	 'usage' => '[galaxy name | seedlist]',
	]);
