<?php

// NOTE: this is only for history database games

// Get old account ID's
$db = new SmrMySqlDatabase();
$oldAccountId = $account->getOldAccountID($var['HistoryDatabase']);

$db = new $var['HistoryDatabase']();

$gameId = $var['view_game_id'];
$gameName = $var['game_name'];

$template->assign('PageTopic', 'Hall of Fame : ' . $gameName);
Menu::history_games(2);

$PHP_OUTPUT .= '<div align="center">';

if (!isset($var['stat'])) {
	// Display a list of stats available to view
	$db->query('SHOW COLUMNS FROM player_has_stats');
	$PHP_OUTPUT .= create_table();
	while ($db->nextRecord()) {
		$stat = $db->getField('Field');
		if ($stat == 'account_id' || $stat == 'game_id') {
			continue;
		}
		$statDisplay = ucwords(str_replace('_', ' ', $stat));
		$container = $var;
		$container['stat'] = $stat;
		$container['stat_display'] = $statDisplay;
		$PHP_OUTPUT .= '<tr><td class="center">' . create_link($container, $statDisplay) . '</td></tr>';
	}
	$PHP_OUTPUT .= '</table>';
}
else {
	// Link back to overview page
	$container = $var;
	unset($container['stat']);
	unset($container['stat_display']);
	$PHP_OUTPUT .= create_link($container, '&lt;&lt;Back');

	// Rankings display
	$PHP_OUTPUT .= '<br /><br /><h2>Rankings: ' . $var['stat_display'] . '</h2>';
	$db->query('SELECT * FROM player_has_stats s INNER JOIN player p ON (p.account_id = s.account_id AND p.game_id = s.game_id) WHERE s.game_id=' . $db->escapeNumber($gameId) . ' ORDER BY s.' . $var['stat'] . ' DESC LIMIT 25');
	if ($db->getNumRows() > 0) {
		$PHP_OUTPUT .= create_table();
		$rank = 1;
		while ($db->nextRecord()) {
			$boldClass = $db->getInt('account_id') == $oldAccountId ? 'class="bold"' : '';
			$PHP_OUTPUT .= '<tr ' . $boldClass . '>';
			$PHP_OUTPUT .= '<td class="center">' . $rank++ . '</td>';
			$PHP_OUTPUT .= '<td>' . stripslashes($db->getField('player_name')) . '</td>';
			$PHP_OUTPUT .= '<td class="center">' . $db->getInt($var['stat']) . '</td>';
			$PHP_OUTPUT .= '</tr>';
		}
		$PHP_OUTPUT .= '</table>';
	} else {
		$PHP_OUTPUT .= 'We apologize, but this stat does not exist for this game!';
	}
}

$PHP_OUTPUT .= '</div>';

$db = new SmrMySqlDatabase();
