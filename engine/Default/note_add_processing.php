<?php declare(strict_types=1);

// Adds a new note into the database
$note = Request::get('note');
if (strlen($note) > 1000) {
	create_error('Note cannot be longer than 1000 characters.');
}

$note = htmlentities($note, ENT_QUOTES, 'utf-8');
$note = nl2br($note);
$db->query('INSERT INTO player_has_notes (player_id,game_id,note) VALUES(' .
		$db->escapeNumber($player->getPlayerID()) . ',' .
		$db->escapeNumber($player->getGameID()) . ',' .
		$db->escapeBinary(gzcompress($note)) . ')');

forward(create_container('skeleton.php', 'trader_status.php'));
