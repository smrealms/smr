<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');

// create planet object
$planet =& $player->getSectorPlanet();
$template->assign('PageTopic','Planet : '.$planet->getName().' [Sector #'.$player->getSectorID().']');

require_once(get_file_loc('menu.inc'));
create_planet_menu();

require_once(get_file_loc('Research.class.inc'));
$research = new Research($player->getGameID());

$researchInProgressArr = $research->getAllianceResearchInProgress($player->getAllianceID());
$researchableCertArr = $research->getAllianceResearchableShipCertificates($player->getAllianceID());

$planetInResearch = false;
foreach($researchInProgressArr as &$z){
    if($z['sector_id']==$player->getSectorID()){
        $template->assign("PlanetResearching", $z);
        $planetInResearch = true;
    }
}

$playerIsResearching = $research->isPlayerResearching($researchInProgressArr, $player->getPlayerID());
//$planetInResearch = $research->isPlanetInResearch($player->getSectorPlanet()->getSectorID());
if(!$planetInResearch && !$playerIsResearching){
    foreach($researchableCertArr AS &$r){
        $container = create_container('skeleton.php', 'research_process.php');
        $container['researchCertificate'] = $r['id'];
        $container['gameId'] = $player->getGameID();
        $r['ResearchCertificateHRF'] = SmrSession::getNewHREF($container);
    }
}

$template->assign("PlayerResearching", $playerIsResearching);
$template->assign("ResearchInProgress", $researchInProgressArr);
$template->assign('ResearchedCertificates', $research->getAllianceResearchedCertificates($player->getAllianceID()));
$template->assign('ResearchableCertificates', $researchableCertArr);
?>