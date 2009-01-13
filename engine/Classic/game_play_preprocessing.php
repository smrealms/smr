<?

if (SmrSession::$game_id > 0)
	$account->log(2, "Player left game SmrSession::$game_id", $player->sector_id);

// reset game id
SmrSession::$game_id = 0;
SmrSession::$update();

forward(create_container("skeleton.php", "game_play.php"));

?>