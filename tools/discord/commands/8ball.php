<?php declare(strict_types=1);

require_once(TOOLS . 'chat_helpers/channel_msg_8ball.php');

$fn_8ball = function($message, $params) {
	if (empty($params)) {
		$message->reply('do you have a question for the magic 8-ball?');
	} else {
		$message->reply(shared_channel_msg_8ball());
	}
};

$discord->registerCommand('8ball', mysql_cleanup($fn_8ball),
	['description' => 'Ask a question, get a magic 8-ball answer.',
	 'usage' => '[question]',
	]);
