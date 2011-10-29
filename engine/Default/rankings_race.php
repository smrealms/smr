<?php

$template->assign('PageTopic','Racial Standings');

require_once(get_file_loc('menu.inc'));
create_ranking_menu(2, 0);

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
$db2 = new SmrMySqlDatabase();
$db->query('SELECT race_id, race_name, SUM(experience) as experience_sum, COUNT(*) as members FROM player JOIN race USING(race_id) WHERE game_id = '.$player->getGameID().' GROUP BY race_id ORDER BY experience_sum DESC');
while ($db->nextRecord())
{
	$rank++;
	$race_id = $db->getInt('race_id');
	$db2->query('SELECT * FROM player WHERE race_id = '.$race_id.' AND game_id = '.$player->getGameID().' AND out_of_game = \'TRUE\'');
	if ($player->getRaceID() == $race_id) $style = ' class="bold"';
	elseif ($db2->nextRecord()) $style = ' class="red"';
	else $style = '';
	
//	if ($db2->nextRecord()) $style .= 
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

?>
