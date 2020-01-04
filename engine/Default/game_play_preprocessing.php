<?php

// Reset the game ID if necessary
if (SmrSession::hasGame()) {
	$account->log(LOG_TYPE_GAME_ENTERING, 'Player left game ' . SmrSession::getGameID());
	SmrSession::updateGame(0);
}

SmrSession::clearLinks();

forward(create_container('skeleton.php', 'game_play.php', $var));
