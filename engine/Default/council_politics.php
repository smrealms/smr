<?php declare(strict_types=1);

if (!isset($var['race_id'])) {
	SmrSession::updateVar('race_id', $player->getRaceID());
}
$raceID = $var['race_id'];

$template->assign('PageTopic', 'Ruling Council Of ' . Globals::getRaceName($raceID));

// echo menu
Menu::council($raceID);

$raceRelations = Globals::getRaceRelations($player->getGameID(), $raceID);

$peaceRaces = array();
$neutralRaces = array();
$warRaces = array();
foreach (Globals::getRaces() as $otherRaceID => $raceInfo) {
	if ($otherRaceID != RACE_NEUTRAL && $raceID != $otherRaceID) {
		if ($raceRelations[$otherRaceID] >= RELATIONS_PEACE) {
			$peaceRaces[$otherRaceID] = $raceInfo;
		} else if ($raceRelations[$otherRaceID] <= RELATIONS_WAR) {
			$warRaces[$otherRaceID] = $raceInfo;
		} else {
			$neutralRaces[$otherRaceID] = $raceInfo;
		}
	}
}

$template->assign('PeaceRaces', $peaceRaces);
$template->assign('NeutralRaces', $neutralRaces);
$template->assign('WarRaces', $warRaces);
