<?php declare(strict_types=1);

use Smr\Database;

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();
$alliance = $player->getAlliance();

$template->assign('PageTopic', 'Alliance Treaties');
Menu::alliance($alliance->getAllianceID());

$alliances = [];
$db = Database::getInstance();
$dbResult = $db->read('SELECT * FROM alliance WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id != ' . $db->escapeNumber($player->getAllianceID()) . ' ORDER BY alliance_name');
foreach ($dbResult->records() as $dbRecord) {
	$allianceID = $dbRecord->getInt('alliance_id');
	$alliance = SmrAlliance::getAlliance($allianceID, $player->getGameID(), false, $dbRecord);
	$alliances[$allianceID] = $alliance->getAllianceDisplayName();
}
$template->assign('Alliances', $alliances);

if (isset($var['message'])) {
	$template->assign('Message', $var['message']);
}

$offers = [];
$dbResult = $db->read('SELECT * FROM alliance_treaties WHERE alliance_id_2 = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . ' AND official = \'FALSE\'');
foreach ($dbResult->records() as $dbRecord) {
	$offerTerms = [];
	foreach (array_keys(SmrTreaty::TYPES) as $term) {
		if ($dbRecord->getBoolean($term)) {
			$offerTerms[] = $term;
		}
	}
	$otherAllianceID = $dbRecord->getInt('alliance_id_1');
	$container = Page::create('alliance_treaties_processing.php');
	$container['alliance_id_1'] = $otherAllianceID;
	$container['aa_access'] = $dbRecord->getBoolean('aa_access');
	$container['accept'] = true;
	$acceptHREF = $container->href();
	$container['accept'] = false;
	$rejectHREF = $container->href();

	$offers[] = [
		'Alliance' => SmrAlliance::getAlliance($otherAllianceID, $player->getGameID()),
		'Terms' => $offerTerms,
		'AcceptHREF' => $acceptHREF,
		'RejectHREF' => $rejectHREF,
	];
}
$template->assign('Offers', $offers);

$container = Page::create('alliance_treaties_confirm.php');
$template->assign('SendOfferHREF', $container->href());
