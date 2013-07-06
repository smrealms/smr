<?php

$template->assign('PageTopic','Operation Rankings');

require_once(get_file_loc('Rankings.inc'));
require_once(get_file_loc('menu.inc'));
create_ranking_menu(0, 0);

// get score information for the player
$opRanking = Rankings::getPlayerOperationScore($db,$player);

$gameRankings = Rankings::getGameOperationRanking($db, $player->getGameID());

$ourRank = 1;
foreach($gameRankings as $rank=>$obj){
    if($obj['player_id']==$player->getPlayerID()){
        $ourRank = $rank+1;
        break;
    }
}

$template->assign('OurRank', $ourRank);

$totalPlayers = $player->getGame()->getTotalPlayers();

$template->assignByRef('Rankings', Rankings::collectRankingsViaArray($gameRankings, $player, 0,10));

Rankings::calculateMinMaxRanks($ourRank, $totalPlayers);

$template->assign('FilterRankingsHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'rankings_player_operation.php')));

$lowerLimit = $var['MinRank'] - 1;
$template->assignByRef('FilteredRankings', Rankings::collectRankingsViaArray($gameRankings, $player, $lowerLimit, $var['MaxRank'] - $lowerLimit));
?>