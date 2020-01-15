<?php declare(strict_types=1);

$game = SmrGame::getGame($_POST['game_id']);
$game->setEnabled(true);
$game->save(); // because next page queries database

$msg = '<span class="green">SUCCESS: </span>Enabled game ' . $game->getDisplayName();

forward(create_container('skeleton.php', 'enable_game.php',
                         array('processing_msg' => $msg)));
