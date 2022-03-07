<?php declare(strict_types=1);

$fn_game = function($message) {
	$link = new GameLink($message);
	if (!$link->valid) {
		return;
	}

	$game = SmrGame::getGame($link->player->getGameID(), true);
	$msg = 'I am linked to game `' . $game->getDisplayName() . '` in this channel.';

	$message->reply($msg)->done(null, 'logException');
};

$discord->registerCommand('game', mysql_cleanup($fn_game), ['description' => 'Get name of game linked to this channel']);
