<?php

function get_turns_message($player) {
	// turns only update when the player is active, so calculate current turns
	$turns = min($player->getTurns() + $player->getTurnsGained(time(), true),
	             $player->getMaxTurns());
	$msg = $player->getPlayerName() . " has $turns/" . $player->getMaxTurns() . " turns.";

	// Calculate time to max turns if under the max
	$timeToMax = $player->getTimeUntilMaxTurns(time(), true);
	if ($timeToMax > 0) {
		$msg .= " At max turns in " . format_time($timeToMax, true) . ".";
	}

	return $msg;
}

$fn_turns = function ($message) {
	$link = new GameLink($message->channel, $message->author);
	if (!$link->valid) return;

	$msg = get_turns_message($link->player);
	$message->channel->sendMessage($msg);
};

$fn_turns_all = function ($message) {
	$link = new GameLink($message->channel, $message->author);
	if (!$link->valid) return;
	$player = $link->player;

	$results = array_map('get_turns_message', $player->getSharingPlayers(true));
	$message->channel->sendMessage(join("\n", $results));
};

$cmd_turns = $discord->registerCommand('turns', mysql_cleanup($fn_turns), ['description' => 'Get current turns']);

$cmd_turns->registerSubCommand('all', mysql_cleanup($fn_turns_all), ['description' => 'Get current turns for all players whose info is shared with you']);
