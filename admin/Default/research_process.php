<?php

require_once(get_file_loc('Research.class.inc'));

$request = $_REQUEST;
$research = new Research();

if( isset($request['addCertificate']) && isset($request['gameResearchId'])){
    $gameResearchId = $request['gameResearchId'];
    $label = $request['label'] ?: "Certificate_".rand(100,1000);
    $raceId = $request['raceId'] ?: null;
    $duration = $request['duration'] ?: 24;
    $iteration = $request['iteration'] ?:1;
    $parentId = $request['parentId'] ?: null;
    $combinedResearch = $request['gameResearchId'] ?: null;

    $r = $research->addResearchCertificate($gameResearchId,$label, $raceId, $duration, $iteration, $parentId, $combinedResearch);

}

if(isset($request['assignCertificate']) && isset($request['gameResearchId'])){
    if(isset($request['researchCertificateId']) && isset($request['shipTypeId'])){
        $research->assignResearchCertificateToShipType($request['researchCertificateId'], $request['shipTypeId'],$request['parentId']);
    }
}

if(isset($var['deleteResearchCertificate']) && isset($var['gameResearchId'])){
    $research->deleteResearchCertificate($var['deleteResearchCertificate']);
}

if(isset($var['deleteResearchShipCertificate']) && isset($var['gameResearchId'])){
    $research->deleteResearchShipCertificate($var['deleteResearchShipCertificate']);
}


$container = create_container('skeleton.php', 'research_ship_view.php');
$container['gameResearchId'] = isset($request['gameResearchId']) ? $request['gameResearchId']: $var['gameResearchId'];
forward($container);


?>