<?php

// check if our alignment is high enough
if ($player->getAlignment() <= -100)
	create_error('You are not allowed to enter our Government HQ!');

// get the name of this facility
$db->query('SELECT * FROM location JOIN location_type USING(location_type_id) JOIN location_is_hq USING(location_type_id) ' .
			'WHERE game_id = '.$player->getGameID().' AND ' .
			'sector_id = '.$player->getSectorID().' AND ' .
			'location.location_type_id = '.$var['LocationID']);
if ($db->nextRecord())
{
	$location_type_id = $db->getField('location_type_id');
	$location_name = $db->getField('location_name');

	$race_id = $location_type_id - 101;
}
else
{
	throw new Exception('Unable to find that hq.');
}

// did we get a result
if (!isset($race_id))
	create_error('There is no headquarter. Obviously.');

// are we at war?
$db->query('SELECT * FROM race_has_relation WHERE game_id = '.SmrSession::$game_id.' AND race_id_1 = '.$race_id.' AND race_id_2 = '.$player->getRaceID());
if ($db->nextRecord() && $db->getField('relation') <= -300)
	create_error('We are at WAR with your race! Get outta here before I call the guards!');

// topic
if (isset($location_type_id))
	$template->assign('PageTopic',$location_name);
else
	$template->assign('PageTopic','Federal Headquarters');

// header menue
include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_hq_menue();

// secondary db object
$races =& Globals::getRaces();
$PHP_OUTPUT.='<div align="center">';
if (isset($location_type_id))
{
	$db->query('SELECT * FROM race_has_relation WHERE game_id = '.$player->getGameID().' AND race_id_1 = '.$race_id);
	if($db->getNumRows()>0)
	{
		$PHP_OUTPUT.=('We are at WAR with<br /><br />');
		while($db->nextRecord())
		{
			if ($db->getField('relation') <= -300)
				$PHP_OUTPUT.=('<span class="red">The '.$races[$db->getField('race_id_2')]['Race Name'].'<br /></span>');
	
		}
		$PHP_OUTPUT.=('<br />The government will PAY for the destruction of their ships!');
	}
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