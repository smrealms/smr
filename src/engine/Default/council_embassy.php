<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

if (!$player->isPresident()) {
	create_error('Only the president can view the embassy.');
}

$template->assign('PageTopic', 'Ruling Council Of ' . $player->getRaceName());

Menu::council($player->getRaceID());

$voteRaces = [];
foreach (Smr\Race::getPlayableIDs() as $raceID) {
	if ($raceID == $player->getRaceID()) {
		continue;
	}
	$dbResult = $db->read('SELECT 1 FROM race_has_voting
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND race_id_1 = ' . $db->escapeNumber($player->getRaceID()) . '
				AND race_id_2 = ' . $db->escapeNumber($raceID));
	if ($dbResult->hasRecord()) {
		continue;
	}
	$voteRaces[$raceID] = Page::create('council_embassy_processing.php', '', ['race_id' => $raceID])->href();
}
$template->assign('VoteRaceHrefs', $voteRaces);
