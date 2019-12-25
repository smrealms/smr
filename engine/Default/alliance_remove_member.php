<?php declare(strict_types=1);
$alliance = $player->getAlliance();
$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($alliance->getAllianceID(), $alliance->getLeaderID());

$db->query('
SELECT
account_id,
player_id,
player_name,
last_cpl_action
FROM player
WHERE game_id = ' . $db->escapeNumber($alliance->getGameID()) . '
AND alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . '
AND account_id != ' . $db->escapeNumber($player->getAccountID()) . '
ORDER BY last_cpl_action DESC
');

$container = create_container('alliance_remove_member_processing.php');
$template->assign('BanishHREF', SmrSession::getNewHREF($container));

$members = [];
while ($db->nextRecord()) {
	// get the amount of time since last_active
	$diff = 864000 + max(-864000, $db->getInt('last_cpl_action') - TIME);
	$lastActive = get_colored_text_range($diff, 864000, date(DATE_FULL_SHORT, $db->getInt('last_cpl_action')));

	$members[] = [
		'last_active' => $lastActive,
		'display_name' => $db->getField('player_name') . ' (' . $db->getInt('player_id') . ')',
		'account_id' => $db->getInt('account_id'),
	];
}
$template->assign('Members', $members);
