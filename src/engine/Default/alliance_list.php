<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'List Of Alliances');

if (!$player->hasAlliance()) {
	$container = Page::create('skeleton.php', 'alliance_create.php');
	$template->assign('CreateAllianceHREF', $container->href());
}


$container = Page::create('skeleton.php');

// get list of alliances
$db = Smr\Database::getInstance();
$db->query('SELECT
count(account_id) as alliance_member_count,
sum(experience) as alliance_xp,
floor(avg(experience)) as alliance_avg,
alliance_name,
alliance_id
FROM player
JOIN alliance USING (game_id, alliance_id)
WHERE leader_id > 0
AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
GROUP BY alliance_id
ORDER BY alliance_name ASC'
);

$alliances = array();
while ($db->nextRecord()) {
	if ($db->getInt('alliance_id') != $player->getAllianceID()) {
		$container['body'] = 'alliance_roster.php';
	} else {
		$container['body'] = 'alliance_mod.php';
	}
	$allianceID = $db->getInt('alliance_id');
	$container['alliance_id'] = $allianceID;

	$alliances[$allianceID] = array(
		'ViewHREF' => $container->href(),
		'Name' => htmlentities($db->getField('alliance_name')),
		'TotalExperience' => $db->getInt('alliance_xp'),
		'AverageExperience' => $db->getInt('alliance_avg'),
		'Members' => $db->getInt('alliance_member_count'),
	);
}
$template->assign('Alliances', $alliances);
