<?php

// check if our alignment is high enough
if ($player->getAlignment() <= ALIGNMENT_EVIL) {
	create_error('You are not allowed to enter our Government HQ!');
}
if(!$player->getSector()->hasLocation($var['LocationID'])) {
	create_error('That location does not exist in this sector');
}
$location = SmrLocation::getLocation($var['LocationID']);
if(!$location->isHQ()) {
	create_error('There is no headquarters. Obviously.');
}
$raceID = $location->getRaceID();

// are we at war?
if ($player->getRelation($raceID) <= RELATIONS_WAR) {
	create_error('We are at WAR with your race! Get outta here before I call the guards!');
}

$template->assign('PageTopic', $location->getName());

// header menu
require_once(get_file_loc('menu_hq.inc'));
create_hq_menu();

$warRaces = [];
if ($raceID != RACE_NEUTRAL) {
	$races = Globals::getRaces();
	$raceRelations = Globals::getRaceRelations($player->getGameID(), $raceID);
	foreach($raceRelations as $otherRaceID => $relation) {
		if ($relation <= RELATIONS_WAR) {
			$warRaces[] = $races[$otherRaceID]['Race Name'];
		}
	}
}
$template->assign('WarRaces', $warRaces);

require_once(get_file_loc('gov.functions.inc'));
$template->assign('AllBounties', getBounties('HQ'));
$template->assign('MyBounties', $player->getClaimableBounties('HQ'));

if ($player->getAlignment() > ALIGNMENT_EVIL && $player->getAlignment() <= ALIGNMENT_GOOD) {
	$container = create_container('government_processing.php');
	transfer('LocationID');
	$template->assign('JoinHREF', SmrSession::getNewHREF($container));
}
