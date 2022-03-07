<?php declare(strict_types=1);

function get_turns_message(SmrPlayer $player): string {
	// turns only update when the player is active, so calculate current turns
	$turns = min($player->getTurns() + $player->getTurnsGained(time(), true),
	             $player->getMaxTurns());
	$msg = $player->getPlayerName() . " has $turns/" . $player->getMaxTurns() . ' turns.';

	// Calculate time to max turns if under the max
	$timeToMax = $player->getTimeUntilMaxTurns(time(), true);
	if ($timeToMax > 0) {
		$msg .= ' At max turns in ' . format_time($timeToMax, true) . '.';
	}

	return $msg;
}

$fn_turns = function($message) {
	$link = new GameLink($message);
	if (!$link->valid) {
		return;
	}

	$results = array_map('get_turns_message', $link->player->getSharingPlayers(true));
	$message->reply(implode("\n", $results))
		->done(null, 'logException');
};

$cmd_turns = $discord->registerCommand('turns', mysql_cleanup($fn_turns), ['description' => 'Show current turns']);
