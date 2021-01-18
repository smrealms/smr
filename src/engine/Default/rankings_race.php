<?php declare(strict_types=1);

$template->assign('PageTopic', 'Racial Standings');

Menu::rankings(2, 0);

$db->query('SELECT race_id, sum(experience) as amount, count(*) as num_players FROM player JOIN race USING(race_id) WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' GROUP BY race_id ORDER BY amount DESC, race_name ASC');
$template->assign('Ranks', Rankings::collectRaceRankings($db, $player));
