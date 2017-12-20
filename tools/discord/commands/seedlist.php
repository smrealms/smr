<?php

require_once(TOOLS . 'chat_helpers/channel_msg_seedlist.php');

$fn_seedlist = function ($message) {
	$link = new GameLink($message->channel, $message->author);
	if (!$link->valid) return;

	// print the entire seedlist
	$results = shared_channel_msg_seedlist($link->player);
	$message->channel->sendMessage(join(EOL, $results));
};

$fn_seedlist_add = function ($message, $sectors) {
	$link = new GameLink($message->channel, $message->author);
	if (!$link->valid) return;

	// add sectors to the seedlist
	$results = shared_channel_msg_seedlist_add($link->player, $sectors);
	$message->channel->sendMessage(join(EOL, $results));
};

$fn_seedlist_del = function ($message, $sectors) {
	$link = new GameLink($message->channel, $message->author);
	if (!$link->valid) return;

	// delete sectors from the seedlist
	$results = shared_channel_msg_seedlist_del($link->player, $sectors);
	$message->channel->sendMessage(join(EOL, $results));
};

$cmd_seedlist = $discord->registerCommand('seedlist', mysql_cleanup($fn_seedlist), ['description' => 'Print the entire seedlist']);

$cmd_seedlist->registerSubCommand('add', mysql_cleanup($fn_seedlist_add), ['description' => 'Add sectors to the seedlist']);

$cmd_seedlist->registerSubCommand('del', mysql_cleanup($fn_seedlist_del), ['description' => 'Delete sectors from the seedlist']);

?>
