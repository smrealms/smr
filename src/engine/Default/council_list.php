<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$raceID = $var['race_id'] ?? $player->getRaceID();

$template->assign('PageTopic', 'Ruling Council Of ' . Smr\Race::getName($raceID));
$template->assign('RaceID', $raceID);

Menu::council($raceID);

// check for relations here
Smr\CouncilVoting::modifyRelations($raceID, $player->getGameID());
Smr\CouncilVoting::checkPacts($raceID, $player->getGameID());
