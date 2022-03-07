<?php declare(strict_types=1);

$game = SmrGame::getGame(Smr\Request::getInt('game_id'));
$game->setEnabled(true);
$game->save(); // because next page queries database

// Create the Newbie Help Alliance
require_once(LIB . 'Default/nha.inc.php');
createNHA($game->getGameID());

$msg = '<span class="green">SUCCESS: </span>Enabled game ' . $game->getDisplayName();

Page::create('skeleton.php', 'admin/enable_game.php',
             ['processing_msg' => $msg])->go();
