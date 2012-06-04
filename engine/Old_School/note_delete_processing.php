<?php

/*
 * Script:
 * 		/engine/Old_School/note_delete_processing.php
 * 
 * Changelog:
 * 		24/10/06 - Created (Curufir)
 * 
 * Notes:
 * 		Deletes selected notes
 */
 
if(isset($_POST['note_id'])) {
	$note_ids = $_POST['note_id'];
	$verified = true;
	foreach($note_ids as $note_id) {
		if(preg_match('/[^0-9]/',$note_id)) {
			$verified = false;
		}
	}
	if($verified) {
		$db->query('DELETE FROM player_has_notes WHERE game_id=' . SmrSession::$game_id .
		' AND account_id=' . SmrSession::$account_id .
		' AND note_id IN ('  . implode(',',$note_ids)  . ')');
	}
}

forward(create_container('skeleton.php', 'trader_status.php'));

?>
