<?php

/*
 * Script:
 * 		/engine/Classic/note_add_processing.php
 * 
 * Changelog:
 * 		24/10/06 - Created (Curufir)
 * 
 * Notes:
 * 		Adds a new note into the database
 */
 
if(isset($_POST['note'])) {
	if(strlen($_POST['note']) < 1000) {
		if(get_magic_quotes_gpc()) {
			$note = stripslashes($_POST['note']);
		}
		else {
			$note = $_POST['note'];
		}
		$note = htmlentities($note,ENT_QUOTES);
		$note = nl2br($note);
		$db->query('INSERT INTO player_has_notes (account_id,game_id,note) VALUES(' .
		$session->account_id . ',' .
		$session->game_id . ',\'' .
		mysql_escape_string(gzcompress($note)) . 
		'\')');
	}	
}

forward(create_container("skeleton.php", "trader_status.php"));
?>
