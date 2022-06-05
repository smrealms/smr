<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Assist Rankings');

Menu::rankings(0, 4);

$rankedStats = Rankings::playerStats('assists', $player->getGameID());

// what rank are we?
$ourRank = Rankings::ourRank($rankedStats, $player->getPlayerID());
$template->assign('OurRank', $ourRank);

$template->assign('Rankings', Rankings::collectRankings($rankedStats, $player));

$totalPlayers = count($rankedStats);
[$minRank, $maxRank] = Rankings::calculateMinMaxRanks($ourRank, $totalPlayers);

$template->assign('FilterRankingsHREF', Page::create('rankings_player_assists.php')->href());

$template->assign('FilteredRankings', Rankings::collectRankings($rankedStats, $player, $minRank, $maxRank));
