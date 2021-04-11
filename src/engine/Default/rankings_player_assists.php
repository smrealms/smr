<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Assist Rankings');

Menu::rankings(0, 4);

// what rank are we?
$ourRank = $player->getAssistsRank();
$template->assign('OurRank', $ourRank);

$totalPlayers = $player->getGame()->getTotalPlayers();

$template->assign('Rankings', Rankings::playerRanks('assists'));

list($minRank, $maxRank) = Rankings::calculateMinMaxRanks($ourRank, $totalPlayers);

$template->assign('FilterRankingsHREF', Page::create('skeleton.php', 'rankings_player_assists.php')->href());

$template->assign('FilteredRankings', Rankings::playerRanks('assists', $minRank, $maxRank));
