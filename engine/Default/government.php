<?php

// check if our alignment is high enough
if ($player->getAlignment() <= ALIGNMENT_EVIL) {
	create_error('You are not allowed to enter our Government HQ!');
}
if(!$player->getSector()->hasLocation($var['LocationID'])) {
	create_error('That location does not exist in this sector');
}
$location =& SmrLocation::getLocation($var['LocationID']);
if(!$location->isHQ()) {
	create_error('There is no headquarters. Obviously.');
}
$raceID = $location->getRaceID();

// are we at war?
if ($player->getRelation($raceID) <= RELATIONS_WAR) {
	create_error('We are at WAR with your race! Get outta here before I call the guards!');
}

$template->assign('PageTopic','Federal Headquarters');

// header menu
require_once(get_file_loc('menu.inc'));
create_hq_menu();

$PHP_OUTPUT.='<div align="center">';
if ($raceID != RACE_NEUTRAL) {
	$races =& Globals::getRaces();
	$raceRelations =& Globals::getRaceRelations($player->getGameID(), $raceID);
	$PHP_OUTPUT.=('We are at WAR with<br /><br />');
	foreach($raceRelations as $otherRaceID => $relation) {
		if ($relation <= RELATIONS_WAR) {
			$PHP_OUTPUT.=('<span class="red">The '.$races[$otherRaceID]['Race Name'].'<br /></span>');
		}
	}
	$PHP_OUTPUT.=('<br />The government will PAY for the destruction of their ships!');
}

require_once(get_file_loc('gov.functions.inc'));
displayBountyList($PHP_OUTPUT,'HQ',0);
displayBountyList($PHP_OUTPUT,'HQ',$player->getAccountID());


if ($player->getAlignment() > ALIGNMENT_EVIL && $player->getAlignment() <= ALIGNMENT_GOOD) {
	$container = create_container('government_processing.php');
	transfer('LocationID');
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=create_submit('Become a deputy');
	$PHP_OUTPUT.=('</form>');
}
$PHP_OUTPUT.='</div>';
