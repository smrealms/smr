<?php

require_once(get_file_loc($var['HistoryDatabase'].'.class.inc'));
$PHP_OUTPUT.=('<div align=center>');

//topic
if (isset($var['game_name'])) $game_name = $var['game_name'];
if (isset($var['game_id'])) $game_id = $var['game_id'];
if (isset($game_name)) $topic = 'Game '.$var['game_name'];
else $topic = 'Games';
$template->assign('PageTopic','Viewing Old SMR '.$topic);

if (!isset($game_name))
{
	//list old games
	$db2 = new $var['HistoryDatabase']();
	$db2->query('SELECT DATE_FORMAT(start_date, \'%c/%e/%Y\') as start_date, ' .
				'DATE_FORMAT(end_date, \'%c/%e/%Y\') as end_date, game_name, speed, game_id ' .
				'FROM game ORDER BY game_id');
	if ($db2->getNumRows())
	{
		$PHP_OUTPUT.=create_table();
		$PHP_OUTPUT.=('<tr><th align=center>Game Name</th><th align=center>Start Date</th><th align=center>End Date</th><th align=center>Speed</th><th align=center colspan=3>Options</th></tr>');
		while ($db2->nextRecord())
		{
			$id = $db2->getField('game_id');
			$container = create_container('skeleton.php','games_previous.php');
			$container['HistoryDatabase'] = $var['HistoryDatabase'];
			$container['game_id'] = $db2->getField('game_id');
			$container['game_name'] = $db2->getField('game_name');
			$name = $db2->getField('game_name');
			$PHP_OUTPUT.=('<tr><td align=center>');
			$PHP_OUTPUT.=create_link($container, $name.' ('.$id.')');
			$PHP_OUTPUT.=('</td>');
			$PHP_OUTPUT.=('<td align=center>' . date(DATE_DATE_SHORT,$db2->getField('start_date')) . '</td>');
			$PHP_OUTPUT.=('<td align=center>' . date(DATE_DATE_SHORT,$db2->getField('end_date')) . '</td>');
			$PHP_OUTPUT.=('<td align=center>' . $db2->getField('speed') . '</td>');
			$PHP_OUTPUT.=('<td align=center>');
			$container = create_container('skeleton.php','hall_of_fame_new.php');
			$container['game_id'] = $db2->getField('game_id');
			$PHP_OUTPUT.=create_link($container, 'Hall of Fame');
			$PHP_OUTPUT.=('</td>');
			$PHP_OUTPUT.=('<td align=center>');
			$container = create_container('skeleton.php','games_previous_news.php');
			$container['HistoryDatabase'] = $var['HistoryDatabase'];
			$container['game_id'] = $db2->getField('game_id');
			$container['game_name'] = $db2->getField('game_name');
			$PHP_OUTPUT.=create_link($container, 'Game News');
			$PHP_OUTPUT.=('</td>');
			$PHP_OUTPUT.=('<td align=center>');
			$container['body'] = 'games_previous_detail.php';
			$PHP_OUTPUT.=create_link($container, 'Game Stats');
			$PHP_OUTPUT.=('</td>');
		}
		$PHP_OUTPUT.=('</table>');

	}
}
else
{
	//code for the game goes in here

	$db2 = new $var['HistoryDatabase']();
	$db2->query('SELECT DATE_FORMAT(start_date, \'%c/%e/%Y\') as start_date, type, ' .
				'DATE_FORMAT(end_date, \'%c/%e/%Y\') as end_date, game_name, speed, game_id ' .
				'FROM game WHERE game_id = '.$game_id);
	$PHP_OUTPUT.=create_table();
	$db2->nextRecord();
	$start = $db2->getField('start_date');
	$end = $db2->getField('end_date');
	$type = $db2->getField('type');
	$speed = $db2->getField('speed');
	$PHP_OUTPUT.='<tr><td align=center>General Info</td><td align=center>Other Info</td></tr>
	<tr>
	<td valign=top align=center>
	<table>
	<tr><td align=right>Name</td>           <td>&nbsp;</td><td align=left>'.$game_name.'</td></tr>
	<tr><td align=right>Start Date</td>     <td>&nbsp;</td><td align=left>'.date(DATE_DATE_SHORT,$start).'</td></tr>
	<tr><td align=right>End Date</td>       <td>&nbsp;</td><td align=left>'.date(DATE_DATE_SHORT,$end).'</td></tr>
	<tr><td align=right>Game Type</td>      <td>&nbsp;</td><td align=left>'.$type.'</td></tr>
	<tr><td align=right>Game Speed</td>     <td>&nbsp;</td><td align=left>'.$speed.'</td></tr>
	</table>
	</td>';
	$db2->query('SELECT * FROM player WHERE game_id = '.$game_id.' ORDER BY experience DESC');
	if ($db2->nextRecord())
	{
		$players = $db2->getNumRows();
		$max_exp = $db2->getField('experience');
	}
	$db2->query('SELECT * FROM player WHERE game_id = '.$game_id.' ORDER BY alignment DESC');
	if ($db2->nextRecord()) $align = $db2->getField('alignment');
	$db2->query('SELECT * FROM player WHERE game_id = '.$game_id.' ORDER BY alignment ASC');
	if ($db2->nextRecord()) $align_low = $db2->getField('alignment');
	$db2->query('SELECT * FROM player WHERE game_id = '.$game_id.' ORDER BY kills DESC');
	if ($db2->nextRecord()) $kills = $db2->getField('kills');


	$db2->query('SELECT * FROM alliance WHERE game_id = '.$game_id);
	if ($db2->nextRecord()) $alliances = $db2->getNumRows();
	$PHP_OUTPUT.='
	<td valign=top align=center>
	<table>
	<tr><td align=right>Players</td>           <td>&nbsp;</td><td align=left>'.$players.'</td></tr>
	<tr><td align=right>Alliances</td>          <td>&nbsp;</td><td align=left>'.$alliances.'</td></tr>
	<tr><td align=right>Highest Experience</td><td>&nbsp;</td><td align=left>'.$max_exp.'</td></tr>
	<tr><td align=right>Highest Alignment</td> <td>&nbsp;</td><td align=left>'.$align.'</td></tr>
	<tr><td align=right>Lowest Alignment</td><td>&nbsp;</td><td align=left>'.$align_low.'</td></tr>
	<tr><td align=right>Highest Kills</td>     <td>&nbsp;</td><td align=left>'.$kills.'</td></tr>
	</table>
	</td>
	</tr>
	</table><br />';
	$PHP_OUTPUT.=create_table();
	$PHP_OUTPUT.='
	<tr>
	<td align=center>Top 10 Players in Experience</td>
	<td align=center>Top 10 Players in Kills</td>
	</tr>
	<tr>
	<td align=center>';
	$rank = 0;
	$db2->query('SELECT * FROM player WHERE game_id = '.$game_id.' ORDER BY experience DESC LIMIT 10');
	if ($db2->getNumRows() > 0)
	{
		$PHP_OUTPUT.=('<table><tr><th align=center>Rank</th><th align=center>Player</th><th align=center>Experience</th></tr>');
		while ($db2->nextRecord())
		{
			$exp = $db2->getField('experience');
			$player_name = stripslashes($db2->getField('player_name'));
			$PHP_OUTPUT.=('<tr><td align=center>' . ++$rank . '</td><td align=center>'.$player_name.'</td><td align=center>'.$exp.'</td></tr>');
		}
		$PHP_OUTPUT.=('</table>');
	}
	$PHP_OUTPUT.='
	</td><td align=center>';
	$rank = 0;
	$db2->query('SELECT * FROM player WHERE game_id = '.$game_id.' ORDER BY kills DESC LIMIT 10');
	if ($db2->getNumRows() > 0)
	{
		$PHP_OUTPUT.=('<table><tr><th align=center>Rank</th><th align=center>Player</th><th align=center>Kills</th></tr>');
		while ($db2->nextRecord())
		{
			$kills = $db2->getField('kills');
			$player_name = stripslashes($db2->getField('player_name'));
			$PHP_OUTPUT.=('<tr><td align=center>' . ++$rank . '</td><td align=center>'.$player_name.'</td><td align=center>'.$kills.'</td></tr>');
		}
		$PHP_OUTPUT.=('</table>');
	}
	$PHP_OUTPUT.='
	</td>
	</tr>
	</table><br />';
	$PHP_OUTPUT.=create_table();
	$PHP_OUTPUT.='<tr><td align=center>Top 10 Alliances in Experience</td><td align=center>Top 10 Alliances in Kills</td></tr>
	<tr>
	<td align=center>';
	$rank = 0;
	//now for the alliance stuff
	$db2->query('SELECT SUM(experience) as exp, alliance_name, alliance_id
				FROM player JOIN alliance USING (game_id, alliance_id)
				WHERE game_id = '.$game_id.' GROUP BY alliance_id ORDER BY exp DESC LIMIT 10');
	if ($db2->getNumRows())
	{
		$PHP_OUTPUT.=('<table><tr><th align=center>Rank</th><th align=center>Alliance</th><th align=center>Experience</th></tr>');
		$container = create_container('skeleton.php', 'alliance_detail_old.php');
		$container['HistoryDatabase'] = $var['HistoryDatabase'];
		$container['game_id'] = $game_id;
		while ($db2->nextRecord())
		{
			$exp = $db2->getField('exp');
			$alliance = stripslashes($db2->getField('alliance_name'));
			$id = $db2->getField('alliance_id');
			$container['alliance_id'] = $id;
			$PHP_OUTPUT.=('<tr><td align=center>' . ++$rank . '</td><td align=center>');
			$PHP_OUTPUT.=create_link($container, $alliance);
			$PHP_OUTPUT.=('</td><td align=center>'.$exp.'</td></tr>');
		}

		$PHP_OUTPUT.=('</table>');
	}
	$PHP_OUTPUT.='
	</td>
	<td valign=top align=center>';
	$rank = 0;
	//now for the alliance stuff
	$db2->query('SELECT kills, alliance_name, alliance_id FROM alliance WHERE game_id = '.$game_id.' ORDER BY kills DESC LIMIT 10');
	if ($db2->getNumRows())
	{
		$PHP_OUTPUT.=('<table><tr><th align=center>Rank</th><th align=center>Alliance</th><th align=center>Kills</th></tr>');
		$container = create_container('skeleton.php', 'alliance_detail_old.php');
		$container['HistoryDatabase'] = $var['HistoryDatabase'];
		$container['game_id'] = $game_id;
		while ($db2->nextRecord())
		{
			$kill = $db2->getField('kills');
			$alliance = stripslashes($db2->getField('alliance_name'));
			$id = $db2->getField('alliance_id');
			$container['alliance_id'] = $id;
			$PHP_OUTPUT.=('<tr><td align=center>' . ++$rank . '</td><td align=center>');
			$PHP_OUTPUT.=create_link($container, $alliance);
			$PHP_OUTPUT.=('</td><td align=center>'.$kill.'</td></tr>');
		}

		$PHP_OUTPUT.=('</table>');

	}
	$PHP_OUTPUT.='
	</td>
	</tr>
	</table><br />';

}
$PHP_OUTPUT.=('</div>');
//to stop errors on the following scripts
$db = new SmrMySqlDatabase();
?>