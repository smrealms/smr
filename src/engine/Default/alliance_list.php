<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'List Of Alliances');

if (!$player->hasAlliance()) {
	$container = Page::create('alliance_create.php');
	$template->assign('CreateAllianceHREF', $container->href());
}

// get list of alliances
$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT
count(account_id) as alliance_member_count,
sum(experience) as alliance_xp,
floor(avg(experience)) as alliance_avg,
alliance.*
FROM player
JOIN alliance USING (game_id, alliance_id)
WHERE leader_id > 0
AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
GROUP BY alliance_id
ORDER BY alliance_name ASC');

$alliances = [];
foreach ($dbResult->records() as $dbRecord) {
	$allianceID = $dbRecord->getInt('alliance_id');
	$alliance = SmrAlliance::getAlliance($allianceID, $player->getGameID(), false, $dbRecord);

	$alliances[$allianceID] = [
		'Name' => $alliance->getAllianceDisplayName(true),
		'TotalExperience' => $dbRecord->getInt('alliance_xp'),
		'AverageExperience' => $dbRecord->getInt('alliance_avg'),
		'Members' => $dbRecord->getInt('alliance_member_count'),
	];
}
$template->assign('Alliances', $alliances);
