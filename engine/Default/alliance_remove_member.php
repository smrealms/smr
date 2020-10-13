<?php declare(strict_types=1);
$alliance = $player->getAlliance();
$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($alliance->getAllianceID(), $alliance->getLeaderID());

$db->query('
SELECT * FROM player
WHERE game_id = ' . $db->escapeNumber($alliance->getGameID()) . '
AND alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . '
AND player_id != ' . $db->escapeNumber($player->getPlayerID()) . '
ORDER BY last_cpl_action DESC
');

$container = create_container('alliance_remove_member_processing.php');
$template->assign('BanishHREF', SmrSession::getNewHREF($container));

$members = [];
while ($db->nextRecord()) {
	$alliancePlayer = SmrPlayer::getPlayer($db->getInt('player_id'), $player->getGameID(), false, $db);

	// get the amount of time since last_active
	$last_cpl_action = $alliancePlayer->getLastCPLAction();
	$diff = 864000 + max(-864000, $last_cpl_action - TIME);
	$lastActive = get_colored_text_range($diff, 864000, date(DATE_FULL_SHORT, $last_cpl_action));

	$members[] = [
		'last_active' => $lastActive,
		'display_name' => $alliancePlayer->getDisplayName(),
		'ccount_id' => $alliancePlayer->getPlayerID(),
	];
}
$template->assign('Members', $members);
