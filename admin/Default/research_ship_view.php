<?php

require_once(get_file_loc('Research.class.inc'));
require_once(get_file_loc('AbstractSmrShip.class.inc'));

if(isset($var['errorMsg'])) {
	$template->assign('ErrorMessage',$var['errorMsg']);
}
if (isset($var['msg'])) {
	$template->assign('Message',$var['msg']);
}

if(isset($_REQUEST['gameId'])) {
    SmrSession::updateVar('gameId',$_REQUEST['gameId']);
}


    $research = new Research($var['gameId']);
    $gr = $research->getGameResearchAss();

    $template->assign("GameResearch",$gr);

    // get races
    $db->query("SELECT * FROM race");
    $races = array();
    while ($db->nextRecord()){
        $races[] = $db->getRow();
    }
    $template->assign("Races",$races);

    $researchCertificates = $research->getResearchCertificates();
    $container = create_container("skeleton.php","research_process.php");
    $container['gameId'] = $gr['game_id'];

    if(isset($researchCertificates)){
        foreach($researchCertificates AS &$cert){
            $container['deleteResearchCertificate']=$cert['id'];
            $cert['deleteHref'] =  SmrSession::getNewHREF($container);
        }
    }


    $template->assign("GameResearchCertificates", $researchCertificates);

    $researchShipCertificates = $research->getResearchShipCertificates();

    $container = create_container("skeleton.php","research_process.php");
    $container['gameId'] = $gr['game_id'];
    if(isset($researchShipCertificates)){
        foreach($researchShipCertificates AS &$cert){
            $container['deleteResearchShipCertificate']=$cert['id'];
            $cert['deleteHref'] =  SmrSession::getNewHREF($container);
        }
    }

    $template->assign("GameResearchShipCertificates", $researchShipCertificates);

    // create game instance ...
    $game = SmrGame::getGame($gr['game_id']);
    $template->assign('Game',$game);

    $container = create_container("skeleton.php","research_process.php");
    $container['gameId'] = $gr['game_id'];
    $template->assign('AddCertificateHref', SmrSession::getNewHREF($container));

    $template->assignByRef('ShipTypes', AbstractSmrShip::getAllBaseShips(0));

?>