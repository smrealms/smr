<?php

function error_on_page($error) {
	$message = '<span class="bold red">ERROR:</span> ' . $error;
	forward(create_container('skeleton.php', 'alliance_set_op.php', array('message' => $message)));
}

if (!empty($var['cancel'])) {
	// just get rid of op
	$db->query('DELETE FROM alliance_has_op WHERE alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ' AND game_id=' . $db->escapeNumber($player->getGameID()));
	$db->query('DELETE FROM alliance_has_op_response WHERE alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ' AND game_id=' . $db->escapeNumber($player->getGameID()));
}
else {
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
}

forward(create_container('skeleton.php', 'alliance_set_op.php'));

?>
