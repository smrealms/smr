<?php declare(strict_types=1);

$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

// Get the selected game
$gameId = $var['selected_game_id'];

// Get the POST variables
$playerId = Smr\Request::getInt('player_id');
$homeSectorID = Smr\Request::getInt('home_sector_id');
$action = Smr\Request::get('submit');

// Pass entire $var so that the selected game remains selected
$container = Page::create('admin/manage_draft_leaders.php', $var);

try {
	$selectedPlayer = SmrPlayer::getPlayerByPlayerID($playerId, $gameId);
} catch (Smr\Exceptions\PlayerNotFound $e) {
	$msg = "<span class='red'>ERROR: </span>" . $e->getMessage();
	$container['processing_msg'] = $msg;
	$container->go();
}

$name = $selectedPlayer->getDisplayName();
$accountId = $selectedPlayer->getAccountID();
$game = $selectedPlayer->getGame()->getDisplayName();

$msg = null; // by default, clear any messages from prior processing
if ($action == 'Assign') {
	if ($selectedPlayer->isDraftLeader()) {
		$msg = "<span class='red'>ERROR: </span>$name is already a draft leader in game $game!";
	} else {
		$db->insert('draft_leaders', [
			'account_id' => $db->escapeNumber($accountId),
			'game_id' => $db->escapeNumber($gameId),
			'home_sector_id' => $db->escapeNumber($homeSectorID),
		]);
	}
} elseif ($action == 'Remove') {
	if (!$selectedPlayer->isDraftLeader()) {
		$msg = "<span class='red'>ERROR: </span>$name is not a draft leader in game $game!";
	} else {
		$db->write('DELETE FROM draft_leaders WHERE ' . $selectedPlayer->getSQL());
	}
} else {
	$msg = "<span class='red'>ERROR: </span>Do not know action '$action'!";
}

$container['processing_msg'] = $msg;
$container->go();
