<?php

$template->assign('PageTopic','Racial Standings');

Menu::rankings(2, 2);

$ranks = [];
$db->query('SELECT race_id, race_name, sum(deaths) as death_sum FROM player JOIN race USING(race_id) WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' GROUP BY race_id ORDER BY death_sum DESC, race_name ASC');
while ($db->nextRecord()) {
	$race_id = $db->getInt('race_id');
	if ($player->getRaceID() == $race_id) $style = ' class="bold"';
	else $style = '';

	$ranks[] = [
		'style' => $style,
		'race_name' => $db->getField('race_name'),
		'death_sum' => $db->getInt('death_sum'),
	];
}
$template->assign('Ranks', $ranks);
