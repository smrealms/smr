<?php

$alliance =& $player->getAlliance();
$template->assign('PageTopic', $alliance->getAllianceName(false, true));
require_once(get_file_loc('menu.inc'));
create_alliance_menu($alliance->getAllianceID(), $alliance->getLeaderID());

$container = create_container('alliance_set_op_processing.php');

// Print any error messages that may have been created
if (!empty($var['message'])) {
	$template->assign('Message', $var['message']);
}

// get the op from db
$db->query('SELECT time FROM alliance_has_op WHERE alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ' AND  game_id=' . $db->escapeNumber($player->getGameID()));

if ($db->nextRecord()) {
	// An op is already scheduled, so get the time
	$time = $db->getInt('time');
	$template->assign('OpDate', date(DATE_FULL_SHORT, $time));
	$template->assign('OpCountdown', format_time($time - TIME));

	// Add a cancel button
	$container['cancel'] = true;
}

$template->assign('OpProcessingHREF', SmrSession::getNewHREF($container));

?>
