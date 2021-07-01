<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Racial Standings');

Menu::rankings(2, 0);

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT race_id, COALESCE(SUM(experience), 0) as amount, count(*) as num_players FROM player JOIN race USING(race_id) WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' GROUP BY race_id ORDER BY amount DESC, race_name ASC');
$template->assign('Ranks', Rankings::collectRaceRankings($dbResult, $player));
