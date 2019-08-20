<?php declare(strict_types=1);

$alliance_id_1 = $player->getAllianceID();
$alliance_id_2 = $_REQUEST['proposedAlliance'];

$db->query('SELECT alliance_id_1, alliance_id_2, game_id FROM alliance_treaties WHERE (alliance_id_1 = ' . $db->escapeNumber($alliance_id_1) . ' OR alliance_id_1 = ' . $alliance_id_2 . ') AND (alliance_id_2 = ' . $db->escapeNumber($alliance_id_1) . ' OR alliance_id_2 = ' . $db->escapeNumber($alliance_id_2) . ') AND game_id = ' . $db->escapeNumber($player->getGameID()));
if ($db->nextRecord()) {
	$container = create_container('skeleton.php', 'alliance_treaties.php');
	$container['message'] = '<span class="red bold">ERROR:</span> There is already an outstanding treaty with that alliance.';
	forward($container);
}

$alliance1 = SmrAlliance::getAlliance($alliance_id_1, $player->getGameID());
$alliance2 = SmrAlliance::getAlliance($alliance_id_2, $player->getGameID());
$template->assign('AllianceName', $alliance2->getAllianceName());

$template->assign('PageTopic', 'Alliance Treaty Confirmation');
Menu::alliance($alliance1->getAllianceID(), $alliance1->getLeaderID());

// Get the terms selected for this offer
$terms = [];
foreach (array_keys(SmrTreaty::TYPES) as $type) {
	$terms[$type] = isset($_REQUEST[$type]);
}
// A few terms get added automatically if a more restrictive term has
// been selected.
$terms['trader_nap'] = $terms['trader_nap'] || $terms['trader_defend'] || $terms['trader_assist'];
$terms['planet_land'] = $terms['planet_land'] || $terms['planet_nap'];
$terms['mb_read'] = $terms['mb_read'] || $terms['mb_write'];
$template->assign('Terms', $terms);

// Create links for yes/no response
$container = create_container('alliance_treaties_confirm_processing.php');
$container['proposedAlliance'] = $alliance_id_2;
foreach ($terms as $term => $value) {
	$container[$term] = $value;
}
$template->assign('YesHREF', SmrSession::getNewHREF($container));

$container = create_container('skeleton.php', 'alliance_treaties.php');
$template->assign('NoHREF', SmrSession::getNewHREF($container));
