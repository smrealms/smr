<?

if ($session->game_id > 0)
	$account->log(2, "Player left game $session->game_id", $player->sector_id);

// reset game id
$session->game_id = 0;
$session->update();

forward(create_container("skeleton.php", "game_play.php"));

?>