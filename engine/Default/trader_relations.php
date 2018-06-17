<?php
$template->assign('PageTopic','Trader Relations');

require_once(get_file_loc('menu.inc'));
create_trader_menu();

$politicalRelations = array();
$personalRelations = array();

$globalRelations = Globals::getRaceRelations($player->getGameID(), $player->getRaceID());
foreach (Globals::getRaces() as $raceID => $race) {
	$politicalRelations[$race['Race Name']] = $globalRelations[$raceID];
	$personalRelations[$race['Race Name']] = $player->getPureRelation($raceID);
}
$template->assign('PoliticalRelations', $politicalRelations);
$template->assign('PersonalRelations', $personalRelations);
