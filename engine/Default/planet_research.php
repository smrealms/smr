<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');

// create planet object
$planet =& $player->getSectorPlanet();
$template->assign('PageTopic','Planet : '.$planet->getName().' [Sector #'.$player->getSectorID().']');

require_once(get_file_loc('menu.inc'));
create_planet_menu();

require_once(get_file_loc('Research.class.inc'));
$research = new Research();

$gameResearch = $research->getGameResearch($player->getGameID());

$template->assign('ResearchedCertificates', $research->getAllianceResearchedCertificates($gameResearch['id'], $player->getAllianceID()));
$template->assign('ResearchableCertificates', $research->getAllianceResearchableCertificates($gameResearch['id'], $player->getAllianceID()));


?>