<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Death Rankings');

Menu::rankings(0, 3);

$rankedStats = Rankings::playerStats('deaths', $player->getGameID());

// what rank are we?
$ourRank = Rankings::ourRank($rankedStats, $player->getPlayerID());
$template->assign('OurRank', $ourRank);

$template->assign('Rankings', Rankings::collectRankings($rankedStats, $player));

$totalPlayers = count($rankedStats);
list($minRank, $maxRank) = Rankings::calculateMinMaxRanks($ourRank, $totalPlayers);

$template->assign('FilterRankingsHREF', Page::create('skeleton.php', 'rankings_player_death.php')->href());

$template->assign('FilteredRankings', Rankings::collectRankings($rankedStats, $player, $minRank, $maxRank));
