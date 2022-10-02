<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Racial Standings');

Menu::rankings(2, 0);

$rankedStats = Rankings::raceStats('experience', $player->getGameID());
$template->assign('Ranks', Rankings::collectRaceRankings($rankedStats, $player));
