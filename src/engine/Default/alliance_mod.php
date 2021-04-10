<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

if (!isset($var['alliance_id'])) {
	$session->updateVar('alliance_id', $player->getAllianceID());
}

$alliance = SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());
$template->assign('Alliance', $alliance);

Globals::canAccessPage('AllianceMOTD', $player, array('AllianceID' => $alliance->getAllianceID()));

$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($alliance->getAllianceID());

// Check to see if an alliance op is scheduled
// Display it for 1 hour past start time (late arrivals, etc.)
$db->query('SELECT time FROM alliance_has_op WHERE alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ' AND game_id=' . $db->escapeNumber($player->getGameID()) . ' AND time > ' . $db->escapeNumber(Smr\Epoch::time() - 3600) . ' LIMIT 1');
if ($db->nextRecord()) {
	$template->assign('OpTime', $db->getInt('time'));

	// Has player responded yet?
	$db2 = Smr\Database::getInstance();
	$db2->query('SELECT response FROM alliance_has_op_response WHERE alliance_id=' . $db2->escapeNumber($player->getAllianceID()) . ' AND ' . $player->getSQL() . ' LIMIT 1');

	$response = $db2->nextRecord() ? $db2->getField('response') : null;
	$responseHREF = Page::create('alliance_op_response_processing.php')->href();
	$template->assign('OpResponseHREF', $responseHREF);

	$responseInputs = array();
	foreach (array('Yes', 'Maybe', 'No') as $option) {
		$style = strtoupper($option) == $response ? 'style="background: green"' : '';
		$responseInputs[$option] = $style;
	}
	$template->assign('ResponseInputs', $responseInputs);
}

// Does the player have edit permission?
$role_id = $player->getAllianceRole($alliance->getAllianceID());
$db->query('SELECT * FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND role_id = ' . $db->escapeNumber($role_id));
$db->requireRecord();
if ($db->getBoolean('change_mod') || $db->getBoolean('change_pass')) {
	$container = Page::create('skeleton.php', 'alliance_stat.php');
	$container['alliance_id'] = $alliance->getAllianceID();
	$template->assign('EditHREF', $container->href());
}

$template->assign('DiscordServer', $alliance->getDiscordServer());
