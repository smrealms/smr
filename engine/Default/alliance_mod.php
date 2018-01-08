<?php
if (!isset($var['alliance_id'])) {
	SmrSession::updateVar('alliance_id',$player->getAllianceID());
}

$alliance =& SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());

Globals::canAccessPage('AllianceMOTD', $player, array('AllianceID' => $alliance->getAllianceID()));

$template->assign('PageTopic', $alliance->getAllianceName(false, true));
require_once(get_file_loc('menu.inc'));
create_alliance_menu($alliance->getAllianceID(),$alliance->getLeaderID());

$PHP_OUTPUT.= '<div align="center">';

// Check to see if an alliance op is scheduled
// Display it for 1 hour past start time (late arrivals, etc.)
$db->query('SELECT time FROM alliance_has_op WHERE alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ' AND game_id=' . $db->escapeNumber($player->getGameID()) . ' AND time > ' . $db->escapeNumber(TIME - 3600) . ' LIMIT 1');
if ($db->nextRecord()) {
	$time = $db->getInt('time');
	$opDate = date(DATE_FULL_SHORT, $time);
	$opCountdown = format_time($time - TIME);

	// Has player responded yet?
	$db2 = new SmrMySqlDatabase();
	$db2->query('SELECT response FROM alliance_has_op_response WHERE alliance_id=' . $db2->escapeNumber($player->getAllianceID()) . ' AND game_id=' . $db2->escapeNumber($player->getGameID()) . ' AND account_id=' . $db2->escapeNumber($player->getAccountID()) . ' LIMIT 1');

	$response = $db2->nextRecord() ? $db2->getField('response') : null;
	$responseHREF = SmrSession::getNewHREF(create_container('alliance_op_response_processing.php'));

	$PHP_OUTPUT .= '<table class="center nobord opResponse">';
	$PHP_OUTPUT .= '<tr><th>ENCRYPTED ALLIANCE TELEGRAM</th></tr>';
	$PHP_OUTPUT .= '<tr><td>Your leader has scheduled an important alliance operation for ' . $opDate . '</td></tr>';
	$PHP_OUTPUT .= '<tr><td><span id="countdown">' . $opCountdown . '</span></td></tr>';
	$PHP_OUTPUT .= '<tr><td><b>Will you join the operation?</b></td></tr>';
	$PHP_OUTPUT .= '<tr><td><form method="POST" action="' . $responseHREF . '">';
	$responseInputs = array();
	foreach (array('Yes', 'No', 'Maybe') as $option) {
		$style = strtoupper($option) == $response ? 'style="background: green"' : '';
		$responseInputs[] = '<input type="submit" name="op_response" ' . $style . ' value="' . $option . '" />';
	}
	$PHP_OUTPUT .= join('&nbsp;', $responseInputs);
	$PHP_OUTPUT .= '</td></tr></table><br />';
}

if ($alliance->hasImageURL()) {
	$PHP_OUTPUT.= '<img class="alliance" src="' . $alliance->getImageURL() . '" alt="' . htmlspecialchars($alliance->getAllianceName()) . ' Banner"><br /><br />';
}

$PHP_OUTPUT.= '<span class="yellow">Message from your leader</span><br /><br />';
$PHP_OUTPUT.= bbifyMessage($alliance->getMotD());

$role_id = $player->getAllianceRole($alliance->getAllianceID());

$db->query('SELECT * FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND role_id = ' . $db->escapeNumber($role_id));
$db->nextRecord();
if ($db->getBoolean('change_mod') || $db->getBoolean('change_pass')) {
	$PHP_OUTPUT.= '<br /><br />';
	$container=create_container('skeleton.php','alliance_stat.php');
	$container['alliance_id'] = $alliance->getAllianceID();
	$PHP_OUTPUT.=create_button($container,'Edit');
}
$PHP_OUTPUT.= '</div>';

?>
