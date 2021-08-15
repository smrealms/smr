<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'NPC Kill Rankings');

Menu::rankings(0, 5);

$hofCategory = ['Killing', 'NPC Kills'];
$rankedStats = Rankings::playerStatsFromHOF($hofCategory, $player->getGameID());

// what rank are we?
$ourRank = Rankings::ourRank($rankedStats, $player->getPlayerID());
$template->assign('OurRank', $ourRank);

$template->assign('Rankings', Rankings::collectRankings($rankedStats, $player));

$totalPlayers = count($rankedStats);
list($minRank, $maxRank) = Rankings::calculateMinMaxRanks($ourRank, $totalPlayers);

$template->assign('FilterRankingsHREF', Page::create('skeleton.php', 'rankings_player_npc_kills.php')->href());

$template->assign('FilteredRankings', Rankings::collectRankings($rankedStats, $player, $minRank, $maxRank));
