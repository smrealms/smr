<?php

$template->assign('PageTopic','List Of Alliances');

if (!$player->hasAlliance()) {
	$container = create_container('skeleton.php','alliance_create.php');
	$template->assign('CreateAllianceHREF', SmrSession::getNewHREF($container));
}


$container = create_container('skeleton.php','alliance_list.php');

if(!isset($var['sequence'])) {
	SmrSession::updateVar('sequence', 'ASC');
}

// get list of alliances
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
ORDER BY ' . $var['order'] . ' ' . $var['sequence']
);

$container['sequence'] = $var['sequence'] == 'DESC' ? 'ASC' : 'DESC';
$alliances = array();
if ($db->getNumRows() > 0) {
	$container['order'] = 'alliance_name';
	$template->assign('SortNameHREF',SmrSession::getNewHREF($container));
	$container['order'] = 'alliance_xp';
	$template->assign('SortTotalExpHREF',SmrSession::getNewHREF($container));
	$container['order'] = 'alliance_avg';
	$template->assign('SortAvgExpHREF',SmrSession::getNewHREF($container));
	$container['order'] = 'alliance_member_count';
	$template->assign('SortMembersHREF',SmrSession::getNewHREF($container));
	while ($db->nextRecord()) {
		if ($db->getField('alliance_id') != $player->getAllianceID()) {
			$container['body'] = 'alliance_roster.php';
		}
		else {
			$container['body'] = 'alliance_mod.php';
		}
		$allianceID = $db->getInt('alliance_id');
		$container['alliance_id'] = $allianceID;

		$alliances[$allianceID] = array(
			'ViewHREF' => SmrSession::getNewHREF($container),
			'Name' => $db->getField('alliance_name'),
			'TotalExperience' => $db->getInt('alliance_xp'),
			'AverageExperience' => $db->getInt('alliance_avg'),
			'Members' => $db->getInt('alliance_member_count'),
		);
	}
}
$template->assign('Alliances', $alliances);
?>