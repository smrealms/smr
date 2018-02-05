<?php
$template->assign('PageTopic','Trader Relations');

require_once(get_file_loc('menu.inc'));
create_trader_menu();

$politicalRelations = array();
$personalRelations = array();

$RACES =& Globals::getRaces();
foreach($RACES as $raceID => $race) {
	$otherRaceRelations = Globals::getRaceRelations($player->getGameID(),$raceID);
	$politicalRelations[$race['Race Name']] = $otherRaceRelations[$player->getRaceID()];
	$personalRelations[$race['Race Name']] = $player->getPureRelation($raceID);
}
$template->assign('PoliticalRelations', $politicalRelations);
$template->assign('PersonalRelations', $personalRelations);

?>
