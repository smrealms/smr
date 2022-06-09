<?php declare(strict_types=1);

$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

// Get the selected game
$game_id = $var['selected_game_id'];

// Get the POST variables
$player_id = Smr\Request::getInt('player_id');
$action = Smr\Request::get('submit');

// Pass entire $var so that the selected game remains selected
$container = Page::create('admin/manage_post_editors.php', $var);

try {
	$selected_player = SmrPlayer::getPlayerByPlayerID($player_id, $game_id);
} catch (Smr\Exceptions\PlayerNotFound $e) {
	$msg = "<span class='red'>ERROR: </span>" . $e->getMessage();
	$container['processing_msg'] = $msg;
	$container->go();
}

$name = $selected_player->getDisplayName();
$account_id = $selected_player->getAccountID();
$game = $selected_player->getGame()->getDisplayName();

$msg = null; // by default, clear any messages from prior processing
if ($action == 'Assign') {
	if ($selected_player->isGPEditor()) {
		$msg = "<span class='red'>ERROR: </span>$name is already an editor in game $game!";
	} else {
		$db->insert('galactic_post_writer', [
			'account_id' => $db->escapeNumber($account_id),
			'game_id' => $db->escapeNumber($game_id),
		]);
	}
} elseif ($action == 'Remove') {
	if (!$selected_player->isGPEditor()) {
		$msg = "<span class='red'>ERROR: </span>$name is not an editor in game $game!";
	} else {
		$db->write('DELETE FROM galactic_post_writer WHERE ' . $selected_player->getSQL());
	}
} else {
	$msg = "<span class='red'>ERROR: </span>Do not know action '$action'!";
}

$container['processing_msg'] = $msg;
$container->go();
