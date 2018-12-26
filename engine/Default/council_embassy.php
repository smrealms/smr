<?php

if(!$player->isPresident()) {
	create_error('Only the president can view the embassy.');
}

require_once(get_file_loc('council.inc'));

$template->assign('PageTopic','Ruling Council Of '.$player->getRaceName());

Menu::council($player->getRaceID());

$voteRaces = array();
$RACES = Globals::getRaces();
foreach($RACES as $raceID => $raceInfo) {
	if ($raceID == RACE_NEUTRAL || $raceID == $player->getRaceID()) {
		continue;
	}
	$db->query('SELECT 1 FROM race_has_voting
				WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND race_id_1 = ' . $db->escapeNumber($player->getRaceID()) . '
				AND race_id_2 = ' . $db->escapeNumber($raceID));
	if ($db->getNumRows() > 0) {
		continue;
	}
	$voteRaces[$raceID] = SmrSession::getNewHREF(create_container('council_embassy_processing.php','',array('race_id' => $raceID)));
}
$template->assign('VoteRaceHrefs',$voteRaces);
