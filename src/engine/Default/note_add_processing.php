<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

// Adds a new note into the database
$note = Smr\Request::get('note');
if (strlen($note) > 1000) {
	create_error('Note cannot be longer than 1000 characters.');
}

$note = htmlentities($note, ENT_QUOTES, 'utf-8');
$note = nl2br($note);
$db = Smr\Database::getInstance();
$db->write('INSERT INTO player_has_notes (account_id,game_id,note) VALUES(' .
		$db->escapeNumber($player->getAccountID()) . ',' .
		$db->escapeNumber($player->getGameID()) . ',' .
		$db->escapeBinary(gzcompress($note)) . ')');

Page::create('skeleton.php', 'trader_status.php')->go();
