<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Kill Rankings');

Menu::rankings(0, 2);

// what rank are we?
$ourRank = $player->getKillsRank();
$template->assign('OurRank', $ourRank);

$totalPlayers = $player->getGame()->getTotalPlayers();

$template->assign('Rankings', Rankings::playerRanks('kills'));

list($minRank, $maxRank) = Rankings::calculateMinMaxRanks($ourRank, $totalPlayers);

$template->assign('FilterRankingsHREF', Page::create('skeleton.php', 'rankings_player_kills.php')->href());

$template->assign('FilteredRankings', Rankings::playerRanks('kills', $minRank, $maxRank));
