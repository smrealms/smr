<?php

$template->assign('PageTopic','Racial Standings');

include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_ranking_menue(2, 0);

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
$db->query('SELECT player.race_id as race_id, race_name, sum(player.experience) as experience_sum, count(player.account_id) as members FROM player NATURAL JOIN race WHERE race.race_id = player.race_id AND player.game_id = '.$player->getGameID().' GROUP BY player.race_id ORDER BY experience_sum DESC');
while ($db->nextRecord())
{
	$rank++;
	$race_id = $db->getField('race_id');
	$db2->query('SELECT * FROM player WHERE race_id = '.$race_id.' AND game_id = '.$player->getGameID().' AND out_of_game = \'TRUE\'');
	if ($player->getRaceID() == $race_id) $style = ' class="bold"';
	elseif ($db2->nextRecord()) $style = ' class="red"';
	else $style = '';
	
//	if ($db2->nextRecord()) $style .= 
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="center"'.$style.'>'.$rank.'</td>');
	$PHP_OUTPUT.=('<td align="center"'.$style.'>' . $db->getField('race_name') . '</td>');
	$PHP_OUTPUT.=('<td align="center"'.$style.'>' . $db->getField('experience_sum') . '</td>');
	$PHP_OUTPUT.=('<td align="center"'.$style.'>' . round($db->getField('experience_sum') / $db->getField('members')) . '</td>');
	$PHP_OUTPUT.=('<td align="center"'.$style.'>' . $db->getField('members') . '</td>');
	$PHP_OUTPUT.=('</tr>');
}

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</div>');

?>
