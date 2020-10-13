<?php declare(strict_types=1);

$note_ids = Request::getIntArray('note_id', []);
if (!empty($note_ids)) {
	$db->query('DELETE FROM player_has_notes WHERE game_id=' . $db->escapeNumber($player->getGameID()) . '
					AND player_id=' . $db->escapeNumber($player->getPlayerID()) . '
					AND note_id IN (' . $db->escapeArray($note_ids) . ')');
}

forward(create_container('skeleton.php', 'trader_status.php'));
