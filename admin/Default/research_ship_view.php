<?php

require_once(get_file_loc('Research.class.inc'));
require_once(get_file_loc('AbstractSmrShip.class.inc'));

if(isset($var['errorMsg'])) {
	$template->assign('ErrorMessage',$var['errorMsg']);
}
if (isset($var['msg'])) {
	$template->assign('Message',$var['msg']);
}

$research = new Research();

if (isset($var['gameId'])) {
    $gameResearch = $research->getGameResearch($var['gameId']);
}

$gameResearchId = $var['gameResearchId'];

if(isset($gameResearchId)){

    // get research entry
    $db->query('SELECT * FROM smr.game_research WHERE id='.$db->escapeNumber($gameResearchId));
    $gameResearch = null;
    if($db->nextRecord()){
        $gameResearch = $db->getRow();
    }
    $template->assign("GameResearch",$gameResearch);

    // get races
    $db->query("SELECT * FROM race");
    $races = [];
    while ($db->nextRecord()){
        $races[] = $db->getRow();
    }
    $template->assign("Races",$races);

    $researchCertificates = $research->getResearchCertificates($gameResearchId);
    $container = create_container("skeleton.php","research_process.php");
    $container['gameResearchId'] = $gameResearchId;

    if(isset($researchCertificates)){
        foreach($researchCertificates AS &$cert){
            $container['deleteResearchCertificate']=$cert['id'];
            $cert['deleteHref'] =  SmrSession::getNewHREF($container);
        }
    }


    $template->assign("GameResearchCertificates", $researchCertificates);

    $researchShipCertificates = $research->getResearchShipCertificates($gameResearchId);

    $container = create_container("skeleton.php","research_process.php");
    $container['gameResearchId'] = $gameResearchId;
    if(isset($researchShipCertificates)){
        foreach($researchShipCertificates AS &$cert){
            $container['deleteResearchShipCertificate']=$cert['id'];
            $cert['deleteHref'] =  SmrSession::getNewHREF($container);
        }
    }

    $template->assign("GameResearchShipCertificates", $researchShipCertificates);

    // create game instance ...
    $game = SmrGame::getGame($gameResearch['game_id']);
    $template->assign('Game',$game);

    $container = create_container("skeleton.php","research_process.php");
    $container['gameResearchId'] = $gameResearchId;
    $template->assign('AddCertificateHref', SmrSession::getNewHREF($container));

    $template->assignByRef('ShipTypes', AbstractSmrShip::getAllBaseShips(0));
}
?>