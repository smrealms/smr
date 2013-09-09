<?php

$template->assign('PageTopic','Alliance Operation Rankings');

require_once(get_file_loc('Rankings.inc'));
require_once(get_file_loc('menu.inc'));
create_ranking_menu(1, 3);

$gameRankings = Rankings::getGameAllianceOperationRanking($db, $player->getGameID());

$ourRank = 0;
if ($player->hasAlliance()) {
    foreach($gameRankings as $rank=>$obj){
         if($obj['alliance_id']==$player->getAllianceID()){
            $ourRank = $rank+1;
            break;
        }
    }
}

$template->assign('OurRank', $ourRank);

$template->assignByRef('Rankings', Rankings::collectAllianceRankingsViaArray($gameRankings, $player, 0,10));

Rankings::calculateMinMaxRanks($ourRank, count($gameRankings));

$template->assign('FilterRankingsHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'rankings_alliance_operation.php')));

$lowerLimit = $var['MinRank'] - 1;
$template->assignByRef('FilteredRankings', Rankings::collectAllianceRankingsViaArray($gameRankings, $player, $lowerLimit, $var['MaxRank'] - $lowerLimit));
?>