<?php declare(strict_types=1);

function error_on_page($error) {
	$message = '<span class="bold red">ERROR:</span> ' . $error;
	forward(create_container('skeleton.php', 'alliance_set_op.php', array('message' => $message)));
}

if (!empty($var['cancel'])) {
	// just get rid of op
	$db->query('DELETE FROM alliance_has_op WHERE alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ' AND game_id=' . $db->escapeNumber($player->getGameID()));
	$db->query('DELETE FROM alliance_has_op_response WHERE alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ' AND game_id=' . $db->escapeNumber($player->getGameID()));

	// Delete the announcement from alliance members message boxes
	$db->query('DELETE FROM message WHERE game_id=' . $db->escapeNumber($player->getGameID()) . ' AND sender_id=' . $db->escapeNumber(ACCOUNT_ID_OP_ANNOUNCE) . ' AND account_id IN (' . $db->escapeArray($player->getAlliance()->getMemberIDs()) . ')');

	// NOTE: for simplicity we don't touch `player_has_unread_messages` here,
	// so they may get an errant alliance message icon if logged in.
} else {
	// schedule an op
	if (empty($_POST['date'])) {
		error_on_page('You must specify a date for the operation!');
	}

	$time = strtotime($_POST['date']);
	if ($time === false) {
		error_on_page('The specified date is not in a valid format.');
	}

	// add op to db
	$db->query('INSERT INTO alliance_has_op (alliance_id, game_id, time) VALUES (' . $db->escapeNumber($player->getAllianceID()) . ', ' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($time) . ')');

	// Send an alliance message that expires at the time of the op.
	// Since the message is procedural, don't exclude this player.
	$message = $player->getBBLink() . ' has scheduled an operation for ' . date(DATE_FULL_SHORT, $time) . '. Navigate to your Alliance console to respond!';
	foreach ($player->getAlliance()->getMemberIDs() as $memberAccountID) {
		$player->sendMessageFromOpAnnounce($memberAccountID, $message, $time);
	}
}

forward(create_container('skeleton.php', 'alliance_set_op.php'));
