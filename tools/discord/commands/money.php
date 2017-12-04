<?php

require_once(TOOLS . 'chat_helpers/channel_msg_money.php');

$fn_money = function ($message) {
	$link = new GameLink($message->channel, $message->author);
	if (!$link->valid) return;

	$result = shared_channel_msg_money($link->player);
	if ($result) {
		$text = implode(EOL, $result);
		$message->channel->sendMessage($text);
	}

	// Close the connection to prevent timeouts
	$db = new SmrMySqlDatabase();
	$db->close();
};

$discord->registerCommand('money', $fn_money, ['description' => 'Get alliance financial status']);

?>
