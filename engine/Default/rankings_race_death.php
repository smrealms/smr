<?php

$template->assign('PageTopic','Racial Standings');

require_once(get_file_loc('menu.inc'));
create_ranking_menu(2, 2);

$PHP_OUTPUT.=('<div align=center>');
$PHP_OUTPUT.=('<p>Here are the rankings of the races by their deaths</p>');
$PHP_OUTPUT.=('<table class="standard" width="95%">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>Rank</th>');
$PHP_OUTPUT.=('<th>Race</th>');
$PHP_OUTPUT.=('<th>Deaths</th>');
$PHP_OUTPUT.=('</tr>');

$rank = 0;
$db2 = new SmrMySqlDatabase();
$db->query('SELECT race_id, race_name, sum(deaths) as death_sum, count(*) FROM player JOIN race USING(race_id) WHERE game_id = '.$player->getGameID().' GROUP BY race_id ORDER BY death_sum DESC');
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
	$PHP_OUTPUT.=('<td align="center"'.$style.'>' . $db->getField('death_sum') . '</td>');
	$PHP_OUTPUT.=('</tr>');
}

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</div>');

?>