<?php declare(strict_types=1);

$template->assign('PageTopic', 'Racial Standings');

Menu::rankings(2, 0);

$ranks = [];
$db->query('SELECT race_id, sum(experience) as exp_sum, count(*) as num_players FROM player JOIN race USING(race_id) WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' GROUP BY race_id ORDER BY exp_sum DESC, race_name ASC');
while ($db->nextRecord()) {
	$race_id = $db->getInt('race_id');
	if ($player->getRaceID() == $race_id) {
		$style = ' class="bold"';
	} else {
		$style = '';
	}

	$ranks[] = [
		'style' => $style,
		'race_id' => $db->getInt('race_id'),
		'exp_sum' => $db->getInt('exp_sum'),
		'exp_avg' => round($db->getInt('exp_sum') / $db->getInt('num_players')),
		'num_players' => $db->getInt('num_players'),
	];
}
$template->assign('Ranks', $ranks);
