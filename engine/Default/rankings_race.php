<?php

$smarty->assign('PageTopic','Racial Standings');

include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_ranking_menue(2, 0);

$PHP_OUTPUT.=('<div align=center>');
$PHP_OUTPUT.=('<p>Here are the rankings of the races by their experience</p>');
$PHP_OUTPUT.=('<table cellspacing="0" cellpadding="5" border="0" class="standard" width="95%">');
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
while ($db->next_record()) {

	$rank++;
	$race_id = $db->f('race_id');
	$db2->query('SELECT * FROM player WHERE race_id = '.$race_id.' AND game_id = '.$player->getGameID().' AND out_of_game = \'TRUE\'');
	if ($player->getRaceID() == $race_id) $style = ' style="font-weight:bold;"';
	elseif ($db2->next_record()) $style = ' style="color:red;"';
	else $style = '';
	
//	if ($db2->next_record()) $style .= 
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="center"'.$style.'>'.$rank.'</td>');
	$PHP_OUTPUT.=('<td align="center"'.$style.'>' . $db->f('race_name') . '</td>');
	$PHP_OUTPUT.=('<td align="center"'.$style.'>' . $db->f('experience_sum') . '</td>');
	$PHP_OUTPUT.=('<td align="center"'.$style.'>' . round($db->f('experience_sum') / $db->f('members')) . '</td>');
	$PHP_OUTPUT.=('<td align="center"'.$style.'>' . $db->f('members') . '</td>');
	$PHP_OUTPUT.=('</tr>');

}

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</div>');

?>
