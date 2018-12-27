<?php

//offer a back button
$container = create_container('skeleton.php', 'games_previous.php');
$container['HistoryDatabase'] = $var['HistoryDatabase'];
$db = new $var['HistoryDatabase']();
$db->query('SELECT * FROM game WHERE game_id = '.$db->escapeNumber($var['game_id']));
$db->nextRecord();
$game_id = $db->getField('game_id');
$container['game_id'] = $game_id;
$container['game_name'] = $db->getField('game_name');

//get alliance members
$id = $var['alliance_id'];
//$PHP_OUTPUT.=($game_id.','. $id);
$db->query('SELECT * FROM alliance WHERE alliance_id = '.$db->escapeNumber($id).' AND game_id = '.$db->escapeNumber($game_id));
$db->nextRecord();
$PHP_OUTPUT.=('<div align=center>');
$PHP_OUTPUT.=create_link($container, '<b>&lt;&lt;Back</b>');
$template->assign('PageTopic','Alliance Roster - ' . stripslashes($db->getField('alliance_name')));

$PHP_OUTPUT.= '
<table class="standard">
	<tr>
		<th>Player Name</th>
		<th>Experience</th>
		<th>Alignment</th>
		<th>Race</th>
		<th>Kills</th>
		<th>Deaths</th>
		<th>Bounty</th>
	</tr>
';

$db->query('SELECT * FROM player WHERE alliance_id = ' . $db->escapeNumber($id) . ' AND game_id = ' . $db->escapeNumber($game_id) . ' ORDER BY experience DESC');
while ($db->nextRecord()) {
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="center">' . $db->getField('player_name') . '</td>');
	$PHP_OUTPUT.=('<td align="center">' . number_format($db->getInt('experience')) . '</td>');
	$PHP_OUTPUT.=('<td align="center">' . number_format($db->getInt('alignment')) . '</td>');
	$PHP_OUTPUT.=('<td align="center">' . number_format($db->getInt('race')) . '</td>');
	$PHP_OUTPUT.=('<td align="center">' . number_format($db->getInt('kills')) . '</td>');
	$PHP_OUTPUT.=('<td align="center">' . number_format($db->getInt('deaths')) . '</td>');
	$PHP_OUTPUT.=('<td align="center">' . number_format($db->getInt('bounty')) . '</td>');
	$PHP_OUTPUT.=('</tr>');
}
$PHP_OUTPUT.=('</table></div>');
$db = new SmrMySqlDatabase();
