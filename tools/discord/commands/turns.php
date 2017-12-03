<?php

$fn_turns = function ($message) {
	$link = new GameLink($message->channel, $message->author);
	if (!$link->valid) return;

	$player = $link->player;
	// turns only update when the player is active, so calculate current turns
	$ship = $player->getShip(true);
	$turns = $player->getTurns() + floor((time() - $player->getLastTurnUpdate()) * $ship->getRealSpeed() / 3600);
	$turns = min($turns, $player->getMaxTurns());

	$msg = $player->getPlayerName() . " has $turns/" . $player->getMaxTurns() . " turns";

	// Calculate time to max turns if under the max
	if ($turns < $player->getMaxTurns()) {
		$maxTime = ceil(($player->getMaxTurns() - $turns) * 3600 / $ship->getRealSpeed());
		$msg .= "\nMax turns will be reached in " . format_time($maxTime, true);
	}

	$message->channel->sendMessage($msg);
};

$discord->registerCommand('turns', $fn_turns, ['description' => 'Get current turns']);

?>
