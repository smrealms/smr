<?php

// check if our alignment is high enough
if ($player->getAlignment() <= -100)
	create_error('You are not allowed to enter our Government HQ!');

// get the name of this facility
$db->query('SELECT 1 FROM location JOIN location_type USING(location_type_id) JOIN location_is_hq USING(location_type_id) ' .
			'WHERE game_id = '.$player->getGameID().' AND ' .
			'sector_id = '.$player->getSectorID().' AND ' .
			'location_type_id = '.$var['LocationID']);
if ($db->nextRecord())
{
	$location =& SmrLocation::getLocation($var['LocationID']);
	$raceID = $location->getRaceID();
}
else
{
	create_error('There is no headquarter. Obviously.');
//	throw new Exception('Unable to find that hq.');
}

// are we at war?
if ($player->getRelation($raceID) <= -300)
	create_error('We are at WAR with your race! Get outta here before I call the guards!');

// topic
if (isset($location))
	$template->assign('PageTopic',$location->getName());
else
	$template->assign('PageTopic','Federal Headquarters');

// header menue
require_once(get_file_loc('menu.inc'));
create_hq_menue();

$PHP_OUTPUT.='<div align="center">';
if (isset($location_type_id))
{
	$races =& Globals::getRaces();
	$raceRelations =& Globals::getRaceRelations($player->getGameID(), $raceID);
	$PHP_OUTPUT.=('We are at WAR with<br /><br />');
	foreach($raceRelations as $otherRaceID => $relation)
	{
		if ($relation <= -300)
			$PHP_OUTPUT.=('<span class="red">The '.$races[$otherRaceID]['Race Name'].'<br /></span>');

	}
	$PHP_OUTPUT.=('<br />The government will PAY for the destruction of their ships!');
}

require_once(get_file_loc('gov.functions.inc'));
displayBountyList($PHP_OUTPUT,'HQ',0);
displayBountyList($PHP_OUTPUT,'HQ',$player->getAccountID());


if ($player->getAlignment() >= -99 && $player->getAlignment() <= 100)
{
	$container = create_container('government_processing.php');
	transfer('LocationID');
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=create_submit('Become a deputy');
	$PHP_OUTPUT.=('</form>');
}
$PHP_OUTPUT.='</div>';
?>