<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Experience Rankings');

Menu::rankings(0, 0);

// what rank are we?
$ourRank = $player->getExperienceRank();
$template->assign('OurRank', $ourRank);

$totalPlayers = $player->getGame()->getTotalPlayers();

$template->assign('Rankings', Rankings::playerRanks('experience'));

list($minRank, $maxRank) = Rankings::calculateMinMaxRanks($ourRank, $totalPlayers);

$template->assign('FilterRankingsHREF', Page::create('skeleton.php', 'rankings_player_experience.php')->href());

$template->assign('FilteredRankings', Rankings::playerRanks('experience', $minRank, $maxRank));
