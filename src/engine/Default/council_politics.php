<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$raceID = $var['race_id'] ?? $player->getRaceID();

$template->assign('PageTopic', 'Ruling Council Of ' . Smr\Race::getName($raceID));

// echo menu
Menu::council($raceID);

$raceRelations = Globals::getRaceRelations($player->getGameID(), $raceID);

$peaceRaces = array();
$neutralRaces = array();
$warRaces = array();
foreach (Smr\Race::getPlayableIDs() as $otherRaceID) {
	if ($raceID != $otherRaceID) {
		if ($raceRelations[$otherRaceID] >= RELATIONS_PEACE) {
			$peaceRaces[$otherRaceID] = $raceInfo;
		} elseif ($raceRelations[$otherRaceID] <= RELATIONS_WAR) {
			$warRaces[$otherRaceID] = $raceInfo;
		} else {
			$neutralRaces[$otherRaceID] = $raceInfo;
		}
	}
}

$template->assign('PeaceRaces', $peaceRaces);
$template->assign('NeutralRaces', $neutralRaces);
$template->assign('WarRaces', $warRaces);
