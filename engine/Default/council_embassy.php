<?php

if(!$player->isPresident())
{
	create_error('Only the president can view the embassy.');
}

require_once(get_file_loc('council.inc'));
require_once(get_file_loc('menu.inc'));

$template->assign('PageTopic','Ruling Council Of '.$player->getRaceName());

create_council_menue($player->getRaceID());

$voteRaces = array();
$RACES = Globals::getRaces();
foreach($RACES as $raceID => $raceInfo)
{
	if ($raceID == RACE_NEUTRAL || $raceID == $player->getRaceID()) continue;
	$db->query('SELECT 1 FROM race_has_voting ' .
				'WHERE game_id = '.$player->getGameID().' AND ' .
					  'race_id_1 = '.$player->getRaceID().' AND ' .
					  'race_id_2 = '.$raceID);
	if ($db->getNumRows() > 0) continue;
	$voteRaces[$raceID] = SmrSession::get_new_href(create_container('council_embassy_processing.php','',array('race_id' => $raceID)));
}
$template->assign('VoteRaceHrefs',$voteRaces)

?>