<?php

$template->assign('PageTopic','Racial Standings');

Menu::rankings(2, 0);

$PHP_OUTPUT.=('<div align=center>');
$PHP_OUTPUT.=('<p>Here are the rankings of the races by their experience</p>');
$PHP_OUTPUT.=('<table class="standard" width="95%">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>Rank</th>');
$PHP_OUTPUT.=('<th>Race</th>');
$PHP_OUTPUT.=('<th>Total Experience</th>');
$PHP_OUTPUT.=('<th>Average Experience</th>');
$PHP_OUTPUT.=('<th>Total Traders</th>');
$PHP_OUTPUT.=('</tr>');

$rank = 0;
$db->query('SELECT race_id, race_name, SUM(experience) as experience_sum, COUNT(*) as members FROM player JOIN race USING(race_id) WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' GROUP BY race_id ORDER BY experience_sum DESC');
while ($db->nextRecord()) {
	$rank++;
	$race_id = $db->getInt('race_id');
	if ($player->getRaceID() == $race_id) $style = ' class="bold"';
	else $style = '';
	
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="center"'.$style.'>'.$rank.'</td>');
	$PHP_OUTPUT.=('<td align="center"'.$style.'>' . $db->getField('race_name') . '</td>');
	$PHP_OUTPUT.=('<td align="center"'.$style.'>' . $db->getInt('experience_sum') . '</td>');
	$PHP_OUTPUT.=('<td align="center"'.$style.'>' . round($db->getInt('experience_sum') / $db->getInt('members')) . '</td>');
	$PHP_OUTPUT.=('<td align="center"'.$style.'>' . $db->getInt('members') . '</td>');
	$PHP_OUTPUT.=('</tr>');
}

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</div>');
