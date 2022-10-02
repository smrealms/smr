<?php declare(strict_types=1);

use Smr\Database;
use Smr\Request;

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$note_ids = Request::getIntArray('note_id', []);
if (!empty($note_ids)) {
	$db = Database::getInstance();
	$db->write('DELETE FROM player_has_notes WHERE game_id=' . $db->escapeNumber($player->getGameID()) . '
					AND account_id=' . $db->escapeNumber($player->getAccountID()) . '
					AND note_id IN (' . $db->escapeArray($note_ids) . ')');
}

Page::create('trader_status.php')->go();
