<?php declare(strict_types=1);

$game = SmrGame::getGame(Smr\Request::getInt('game_id'));
$game->setEnabled(true);
$game->save(); // because next page queries database

// Create the Newbie Help Alliance
require_once(get_file_loc('nha.inc.php'));
createNHA($game->getGameID());

$msg = '<span class="green">SUCCESS: </span>Enabled game ' . $game->getDisplayName();

Page::create('skeleton.php', 'enable_game.php',
             array('processing_msg' => $msg))->go();
