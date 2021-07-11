<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Racial Standings');

Menu::rankings(2, 2);

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT race_id, sum(deaths) as amount, count(*) as num_players FROM player WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' GROUP BY race_id ORDER BY amount DESC');
$template->assign('Ranks', Rankings::collectRaceRankings($dbResult, $player));
