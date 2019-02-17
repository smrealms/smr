<?php

$PHP_OUTPUT.=('<div class="center">');

//topic
if (!isset($var['game_name']) || !isset($var['view_game_id'])) {
	create_error('No game specified!');
}
$game_name = $var['game_name'];
$game_id = $var['view_game_id'];
$topic = 'Game '.$var['game_name'];
$template->assign('PageTopic','Viewing Old SMR '.$topic);


$db2 = new $var['HistoryDatabase']();
$db2->query('SELECT start_date, type, end_date, game_name, speed, game_id ' .
			'FROM game WHERE game_id = '.$db->escapeNumber($game_id));
$db2->nextRecord();
$start = $db2->getField('start_date');
$end = $db2->getField('end_date');
$type = $db2->getField('type');
$speed = $db2->getField('speed');
$PHP_OUTPUT.='<table class="center">
<tr>
<td class="top center">
<table class="standard left">
<tr><th colspan="2">General Info</th></tr>
<tr><td>Name</td><td>'.$game_name.'</td></tr>
<tr><td>Start Date</td><td>'.date(DATE_DATE_SHORT,$start).'</td></tr>
<tr><td>End Date</td><td>'.date(DATE_DATE_SHORT,$end).'</td></tr>
<tr><td>Game Type</td><td>'.$type.'</td></tr>
<tr><td>Game Speed</td><td>'.$speed.'</td></tr>
</table>
</td>';
$db2->query('SELECT * FROM player WHERE game_id = '.$game_id.' ORDER BY experience DESC');
if ($db2->nextRecord()) {
	$players = $db2->getNumRows();
	$max_exp = $db2->getField('experience');
}
$db2->query('SELECT * FROM player WHERE game_id = '.$db->escapeNumber($game_id).' ORDER BY alignment DESC');
if ($db2->nextRecord()) $align = $db2->getField('alignment');
$db2->query('SELECT * FROM player WHERE game_id = '.$db->escapeNumber($game_id).' ORDER BY alignment ASC');
if ($db2->nextRecord()) $align_low = $db2->getField('alignment');
$db2->query('SELECT * FROM player WHERE game_id = '.$db->escapeNumber($game_id).' ORDER BY kills DESC');
if ($db2->nextRecord()) $kills = $db2->getField('kills');


$db2->query('SELECT * FROM alliance WHERE game_id = '.$db->escapeNumber($game_id));
if ($db2->nextRecord()) $alliances = $db2->getNumRows();
$PHP_OUTPUT.='
<td class="top center">
<table class="standard left">
<tr><th colspan="2">Other Info</th></tr>
<tr><td>Players</td><td>'.$players.'</td></tr>
<tr><td>Alliances</td><td>'.$alliances.'</td></tr>
<tr><td>Highest Experience</td><td>'.$max_exp.'</td></tr>
<tr><td>Highest Alignment</td><td>'.$align.'</td></tr>
<tr><td>Lowest Alignment</td><td>'.$align_low.'</td></tr>
<tr><td>Highest Kills</td><td>'.$kills.'</td></tr>
</table>
</td>
</tr>
</table><br />

<table class="center">
<tr>
<td>Top 10 Players in Experience</td>
<td>Top 10 Players in Kills</td>
</tr>
<tr>
<td class="top">';
$rank = 0;
$db2->query('SELECT * FROM player WHERE game_id = '.$db->escapeNumber($game_id).' ORDER BY experience DESC LIMIT 10');
if ($db2->getNumRows() > 0) {
	$PHP_OUTPUT.=('<table class="standard"><tr><th>Rank</th><th>Player</th><th>Experience</th></tr>');
	while ($db2->nextRecord()) {
		$exp = $db2->getField('experience');
		$player_name = stripslashes($db2->getField('player_name'));
		$PHP_OUTPUT.=('<tr><td>' . ++$rank . '</td><td>'.$player_name.'</td><td>'.$exp.'</td></tr>');
	}
	$PHP_OUTPUT.=('</table>');
}
$PHP_OUTPUT.='
</td><td class="top">';
$rank = 0;
$db2->query('SELECT * FROM player WHERE game_id = '.$db->escapeNumber($game_id).' ORDER BY kills DESC LIMIT 10');
if ($db2->getNumRows() > 0) {
	$PHP_OUTPUT.=('<table class="standard"><tr><th>Rank</th><th>Player</th><th>Kills</th></tr>');
	while ($db2->nextRecord()) {
		$kills = $db2->getField('kills');
		$player_name = stripslashes($db2->getField('player_name'));
		$PHP_OUTPUT.=('<tr><td>' . ++$rank . '</td><td>'.$player_name.'</td><td>'.$kills.'</td></tr>');
	}
	$PHP_OUTPUT.=('</table>');
}
$PHP_OUTPUT.='
</td>
</tr>
</table><br />

<table class="center">
<tr><td>Top 10 Alliances in Experience</td><td>Top 10 Alliances in Kills</td></tr>
<tr>
<td class="top">';
$rank = 0;
//now for the alliance stuff
$db2->query('SELECT SUM(experience) as exp, alliance_name, alliance_id
			FROM player JOIN alliance USING (game_id, alliance_id)
			WHERE game_id = '.$db->escapeNumber($game_id).' GROUP BY alliance_id ORDER BY exp DESC LIMIT 10');
if ($db2->getNumRows()) {
	$PHP_OUTPUT.=('<table class="standard"><tr><th>Rank</th><th>Alliance</th><th>Experience</th></tr>');
	$container = create_container('skeleton.php', 'history_alliance_detail.php');
	$container['HistoryDatabase'] = $var['HistoryDatabase'];
	$container['view_game_id'] = $game_id;
	while ($db2->nextRecord()) {
		$exp = $db2->getField('exp');
		$alliance = stripslashes($db2->getField('alliance_name'));
		$id = $db2->getField('alliance_id');
		$container['alliance_id'] = $id;
		$PHP_OUTPUT.=('<tr><td>' . ++$rank . '</td><td>');
		$PHP_OUTPUT.=create_link($container, $alliance);
		$PHP_OUTPUT.=('</td><td>'.$exp.'</td></tr>');
	}

	$PHP_OUTPUT.=('</table>');
}
$PHP_OUTPUT.='
</td>
<td class="top">';
$rank = 0;
//now for the alliance stuff
$db2->query('SELECT kills, alliance_name, alliance_id FROM alliance WHERE game_id = '.$db->escapeNumber($game_id).' ORDER BY kills DESC LIMIT 10');
if ($db2->getNumRows()) {
	$PHP_OUTPUT.=('<table class="standard"><tr><th>Rank</th><th>Alliance</th><th>Kills</th></tr>');
	$container = create_container('skeleton.php', 'history_alliance_detail.php');
	$container['HistoryDatabase'] = $var['HistoryDatabase'];
	$container['view_game_id'] = $game_id;
	while ($db2->nextRecord()) {
		$kill = $db2->getField('kills');
		$alliance = stripslashes($db2->getField('alliance_name'));
		$id = $db2->getField('alliance_id');
		$container['alliance_id'] = $id;
		$PHP_OUTPUT.=('<tr><td>' . ++$rank . '</td><td>');
		$PHP_OUTPUT.=create_link($container, $alliance);
		$PHP_OUTPUT.=('</td><td>'.$kill.'</td></tr>');
	}

	$PHP_OUTPUT.=('</table>');

}
$PHP_OUTPUT.='
</td>
</tr>
</table><br />';

$PHP_OUTPUT.=('</div>');
//to stop errors on the following scripts
$db = new SmrMySqlDatabase();
