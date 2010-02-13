<?php

$template->assign('PageTopic','Racial Standings');

include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_ranking_menue(2, 1);

$PHP_OUTPUT.=('<div align=center>');
$PHP_OUTPUT.=('<p>Here are the rankings of the races by their kills</p>');
$PHP_OUTPUT.=('<table class="standard" width="95%">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>Rank</th>');
$PHP_OUTPUT.=('<th>Race</th>');
$PHP_OUTPUT.=('<th>Kills</th>');
$PHP_OUTPUT.=('</tr>');

$rank = 0;
$db2 = new SmrMySqlDatabase();
$db->query('SELECT race.race_id as race_id, race_name, sum(kills) as kill_sum, count(account_id) FROM player NATURAL JOIN race WHERE game_id = '.$player->getGameID().' GROUP BY player.race_id ORDER BY kill_sum DESC');
while ($db->nextRecord())
{
	$rank++;
	$race_id = $db->getField('race_id');
	$db2->query('SELECT * FROM player WHERE race_id = '.$race_id.' AND game_id = '.$player->getGameID().' AND out_of_game = \'TRUE\'');
	if ($player->getRaceID() == $race_id) $style = ' class="bold"';
	elseif ($db2->nextRecord()) $style = ' class="red"';
	else $style = '';

	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="center"'.$style.'>'.$rank.'</td>');
	$PHP_OUTPUT.=('<td align="center"'.$style.'>' . $db->getField('race_name') . '</td>');
	$PHP_OUTPUT.=('<td align="center"'.$style.'>' . $db->getField('kill_sum') . '</td>');
	$PHP_OUTPUT.=('</tr>');
}

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</div>');

?>