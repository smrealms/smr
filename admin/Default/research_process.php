<?php

require_once(get_file_loc('Research.class.inc'));

$request = $_REQUEST;
$research = new Research( isset($request['gameId'])? $request['gameId'] : $var['gameId'] );
$gr = $research->getGameResearchAss();

$container = create_container('skeleton.php', 'research_ship_view.php');
$container['gameId'] = isset($request['gameId']) ? $request['gameId']: $var['gameId'];

if( isset($var['researchCertificate'])){

    $result = $research->startShipResearch($player, $var['researchCertificate']);
    if(isset($result['error'])){
        create_error($result['error']);
    }
    $container = create_container('skeleton.php', 'planet_research.php');
    $template->assign("Msg",$result['success']);
    $container['gameId'] = isset($request['gameId']) ? $request['gameId']: $var['gameId'];
    forward($container);
}

if( isset($request['addCertificate'])){
    $label = (isset($request['label']) && !empty($request['label'])) ? $request['label'] : "Certificate_".rand(100,1000);
    $raceId = (isset($request['raceId']) && !empty($request['raceId']))  ? $request['raceId'] : null;
    $duration = (isset($request['duration']) && !empty($request['duration'])) ? $request['duration'] : 24;
    $iteration = (isset($request['iteration']) && !empty($request['iteration'])) ? $request['iteration'] : 1;
    $parentId = (isset($request['parentId']) && !empty($request['parentId'])) ? $request['parentId'] : null;
    $credits = (isset($request['credits']) && !empty($request['credits']))  ? $request['credits'] : 0;
    $computer = (isset($request['computer']) && !empty($request['computer'])) ? $request['computer'] : 0;
    $combinedResearch = (isset($request['combinedResearch']) && !empty($request['combinedResearch'])) ? $request['combinedResearch'] : null;

    $r = $research->addResearchCertificate($label, $raceId, $duration, $iteration, $parentId, $combinedResearch, $credits, $computer);
}

if(isset($request['assignCertificate'])){
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


forward($container);
?>