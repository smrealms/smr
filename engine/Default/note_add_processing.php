<?php

/*
 * Script:
 * 		/engine/Default/note_add_processing.php
 *
 * Changelog:
 * 		24/10/06 - Created (Curufir)
 *
 * Notes:
 * 		Adds a new note into the database
 */
 
if(isset($_REQUEST['note'])) {
	$note = $_REQUEST['note'];
	if(strlen($note) > 1000)
		create_error('Note cannot be longer than 1000 characters.');

	$note = htmlentities($note,ENT_QUOTES,'utf-8');
	$note = nl2br($note);
	$db->query('INSERT INTO player_has_notes (account_id,game_id,note) VALUES(' .
		$db->escapeNumber($player->getAccountID()) . ',' .
		$db->escapeNumber($player->getGameID()) . ',' .
		$db->escapeBinary(gzcompress($note)) . ')');
}

forward(create_container('skeleton.php', 'trader_status.php'));
