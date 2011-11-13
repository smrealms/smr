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
 
if(isset($_POST['note']))
{
	if(strlen($_POST['note']) < 1000)
	{
		$note = htmlentities($note,ENT_QUOTES,'utf-8');
		$note = nl2br($note);
		$db->query('INSERT INTO player_has_notes (account_id,game_id,note) VALUES(' .
			$player->getAccountID() . ',' .
			$player->getGameID() . ',' .
			$db->escapeBinary(gzcompress($note)) .
			')');
	}
}

forward(create_container('skeleton.php', 'trader_status.php'));
?>