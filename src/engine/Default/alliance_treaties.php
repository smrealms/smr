<?php declare(strict_types=1);

$template->assign('PageTopic', 'Alliance Treaties');
$alliance = $player->getAlliance();
Menu::alliance($alliance->getAllianceID(), $alliance->getLeaderID());

$alliances = [];
$db->query('SELECT * FROM alliance WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id != ' . $db->escapeNumber($player->getAllianceID()) . ' ORDER BY alliance_name');
while ($db->nextRecord()) {
	$alliances[$db->getInt('alliance_id')] = htmlentities($db->getField('alliance_name'));
}
$template->assign('Alliances', $alliances);

if (isset($var['message'])) {
	$template->assign('Message', $var['message']);
}

$offers = [];
$db->query('SELECT * FROM alliance_treaties WHERE alliance_id_2 = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . ' AND official = \'FALSE\'');
while ($db->nextRecord()) {
	$offerTerms = [];
	foreach (array_keys(SmrTreaty::TYPES) as $term) {
		if ($db->getBoolean($term)) {
			$offerTerms[] = $term;
		}
	}
	$otherAllianceID = $db->getInt('alliance_id_1');
	$container = create_container('alliance_treaties_processing.php', '');
	$container['alliance_id_1'] = $otherAllianceID;
	$container['aa_access'] = $db->getField('aa_access');
	$container['accept'] = true;
	$acceptHREF = SmrSession::getNewHREF($container);
	$container['accept'] = false;
	$rejectHREF = SmrSession::getNewHREF($container);

	$offers[] = [
		'Alliance' => SmrAlliance::getAlliance($otherAllianceID, $player->getGameID()),
		'Terms' => $offerTerms,
		'AcceptHREF' => $acceptHREF,
		'RejectHREF' => $rejectHREF,
	];
}
$template->assign('Offers', $offers);

$container = create_container('skeleton.php', 'alliance_treaties_confirm.php');
$template->assign('SendOfferHREF', SmrSession::getNewHREF($container));
