<?php

$game_name = $var['game_name'];
$game_id = $var['view_game_id'];
$template->assign('PageTopic',$game_name.' - Extended Stats');
Menu::history_games(1);

$action = SmrSession::getRequestVar('action');
$PHP_OUTPUT.=('<div align=center>');
if (empty($action)) {
	$PHP_OUTPUT.=('Click a link to view those stats.<br /><br />');
	$container = create_container('skeleton.php','history_games.php');
	$container['HistoryDatabase'] = $var['HistoryDatabase'];
	$container['view_game_id'] = $game_id;
	$container['game_name'] = $game_name;
	$PHP_OUTPUT.=create_link($container, '<b>Basic Game Stats</b>');
	$PHP_OUTPUT.=('<br />');
	$container['body'] = 'history_games_detail.php';
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=create_submit('Top Mined Sectors');
	$PHP_OUTPUT.=('<br />');
	$PHP_OUTPUT.=create_submit('Sectors with most Forces');
	$PHP_OUTPUT.=('<br />');
	$PHP_OUTPUT.=create_submit('Top Killing Sectors');
	$PHP_OUTPUT.=('<br />');
	$PHP_OUTPUT.=create_submit('Top Planets');
	$PHP_OUTPUT.=('<br />');
	$PHP_OUTPUT.=create_submit('Top Alliance Experience');
	$PHP_OUTPUT.=('<br />');
	$PHP_OUTPUT.=create_submit('Top Alliance Kills');
	$PHP_OUTPUT.=('<br />');
	$PHP_OUTPUT.=create_submit('Top Alliance Deaths');
	$PHP_OUTPUT.=('</form>');
	$PHP_OUTPUT.=('<br />');
}
else {
	if ($action == 'Top Mined Sectors') { $sql = 'mines'; $from = 'sector'; $dis = 'Mines'; }
	elseif ($action == 'Sectors with most Forces') { $sql = 'mines + combat + scouts'; $from = 'sector'; $dis = 'Forces'; }
	elseif ($action == 'Top Killing Sectors') { $sql = 'kills'; $from = 'sector'; $dis = 'Kills'; }
	elseif ($action == 'Top Planets') { $sql = '(turrets + hangers + generators) / 3'; $from = 'planet'; $dis = 'Planet Level'; }
	elseif ($action == 'Top Alliance Experience') { $sql = 'SUM(experience)'; $from = 'player'; $dis = 'Alliance Experience'; $gr = 'dummy';}
	elseif ($action == 'Top Alliance Kills') { $sql = 'kills'; $from = 'alliance'; $dis = 'Alliance Kills'; $gr = 'dummy';}
	elseif ($action == 'Top Alliance Deaths') { $sql = 'deaths'; $from = 'alliance'; $dis = 'Alliance Deaths'; $gr = 'dummy';}

	$db2 = new $var['HistoryDatabase']();
	if (empty($gr)) {
		$db2->query('SELECT '.$sql.' as val, sector_id FROM '.$from.' WHERE game_id = '.$db->escapeNumber($game_id).' '.$gr.' ORDER BY '.$sql.' DESC LIMIT 30');

		$container = create_container('skeleton.php', 'history_games_detail.php');
		$container['HistoryDatabase'] = $var['HistoryDatabase'];
		$container['view_game_id'] = $game_id;
		$container['game_name'] = $game_name;
		$PHP_OUTPUT.=create_link($container, '<b>&lt;&lt;Back</b>');
		$PHP_OUTPUT.=create_table();
		$PHP_OUTPUT.=('<tr><th align=center>Sector ID</th><th align=center>'.$dis.'</th></tr>');
		while ($db2->nextRecord()) {
			$sector_id = $db2->getField('sector_id');
			$val = $db2->getField('val');
			$PHP_OUTPUT.=('<tr><td>'.$sector_id.'</td><td>'.$val.'</td></tr>');
		}
		$PHP_OUTPUT.=('</table>');
	}
	else {
		$sql = 'SELECT alliance_id, '.$sql.' as val FROM '.$from.' WHERE game_id = '.$db->escapeNumber($game_id).' AND alliance_id > 0 GROUP BY alliance_id ORDER BY val DESC LIMIT 30';
		$db2->query($sql);
		$db = new $var['HistoryDatabase']();
		$container = create_container('skeleton.php','history_games_detail.php');
		$container['HistoryDatabase'] = $var['HistoryDatabase'];
		$container['view_game_id'] = $game_id;
		$container['game_name'] = $game_name;
		$PHP_OUTPUT.=create_link($container, '<b>&lt;&lt;Back</b>');
		$PHP_OUTPUT.=create_table();
		$PHP_OUTPUT.=('<tr><th align=center>Alliance ID</th><th align=center>'.$dis.'</th></tr>');
		$container = create_container('skeleton.php', 'history_alliance_detail.php');
		$container['HistoryDatabase'] = $var['HistoryDatabase'];
		$container['view_game_id'] = $game_id;
		$container['selected_index'] = 1;
		while ($db2->nextRecord()) {
			$alliance_id = $db2->getField('alliance_id');
			$db->query('SELECT * FROM alliance WHERE alliance_id = '.$db->escapeNumber($alliance_id).' AND game_id = '.$db->escapeNumber($game_id));
			$db->nextRecord();
			$name = stripslashes($db->getField('alliance_name'));
			$val = $db2->getField('val');
			$PHP_OUTPUT.=('<tr><td>');
			$container['alliance_id'] = $alliance_id;
			$PHP_OUTPUT.=create_link($container, $name);
			$PHP_OUTPUT.=('</td><td>'.$val.'</td></tr>');
		}
		$PHP_OUTPUT.=('</table>');
	}
}
$PHP_OUTPUT.=('</div>');
$db = new SmrMySqlDatabase();
