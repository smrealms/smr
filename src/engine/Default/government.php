<?php declare(strict_types=1);

use Smr\BountyType;

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

// check if our alignment is high enough
if ($player->hasEvilAlignment()) {
	create_error('You are not allowed to enter our Government HQ!');
}
if (!$player->getSector()->hasLocation($var['LocationID'])) {
	create_error('That location does not exist in this sector');
}
$location = SmrLocation::getLocation($var['LocationID']);
if (!$location->isHQ()) {
	create_error('There is no headquarters. Obviously.');
}
$raceID = $location->getRaceID();

// are we at war?
if ($player->getRelation($raceID) <= RELATIONS_WAR) {
	create_error('We are at WAR with your race! Get outta here before I call the guards!');
}

$template->assign('PageTopic', $location->getName());

// header menu
Menu::headquarters($var['LocationID']);

$warRaces = [];
if ($raceID != RACE_NEUTRAL) {
	$raceRelations = Globals::getRaceRelations($player->getGameID(), $raceID);
	foreach ($raceRelations as $otherRaceID => $relation) {
		if ($relation <= RELATIONS_WAR) {
			$warRaces[] = Smr\Race::getName($otherRaceID);
		}
	}
}
$template->assign('WarRaces', $warRaces);

$template->assign('AllBounties', Smr\Bounties::getMostWanted(BountyType::HQ));
$template->assign('MyBounties', $player->getClaimableBounties(BountyType::HQ));

if ($player->hasNeutralAlignment()) {
	$container = Page::create('government_processing.php');
	$container->addVar('LocationID');
	$template->assign('JoinHREF', $container->href());
}
