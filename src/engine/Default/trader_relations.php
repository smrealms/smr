<?php declare(strict_types=1);
$template->assign('PageTopic', 'Trader Relations');

Menu::trader();

$politicalRelations = array();
$personalRelations = array();

$raceRelations = Globals::getRaceRelations($player->getGameID(), $player->getRaceID());
foreach (Globals::getRaces() as $raceID => $race) {
	$politicalRelations[$race['Race Name']] = $raceRelations[$raceID];
	$personalRelations[$race['Race Name']] = $player->getPersonalRelation($raceID);
}
$template->assign('PoliticalRelations', $politicalRelations);
$template->assign('PersonalRelations', $personalRelations);
