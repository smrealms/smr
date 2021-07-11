<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

if (!isset($var['race_id'])) {
	$session->updateVar('race_id', $player->getRaceID());
}
$raceID = $var['race_id'];

$template->assign('PageTopic', 'Ruling Council Of ' . Smr\Race::getName($raceID));
$template->assign('RaceID', $raceID);

Menu::council($raceID);

// check for relations here
require_once(get_file_loc('council.inc.php'));
modifyRelations($raceID, $player->getGameID());
checkPacts($raceID, $player->getGameID());
