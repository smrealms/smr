<?php

/*
 * Script:
 * 		/engine/Default/note_delete_processing.php
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
		$db->query('DELETE FROM player_has_notes WHERE game_id=' . $db->escapeNumber($player->getGameID()) . '
					AND account_id=' . $db->escapeNumber($player->getAccountID()) . '
					AND note_id IN (' . $db->escapeArray($note_ids)  . ')');
	}
}

forward(create_container('skeleton.php', 'trader_status.php'));

?>