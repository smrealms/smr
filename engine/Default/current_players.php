<?php

$template->assign('PageTopic','Current Players');
$db->query('DELETE FROM cpl_tag WHERE expires > 0 AND expires < ' . $db->escapeNumber(TIME));
$db->query('SELECT count(*) count FROM active_session
			WHERE last_accessed >= ' . $db->escapeNumber(TIME - 600) . ' AND
				game_id = ' . $db->escapeNumber($player->getGameID()));
$count_real_last_active = 0;
if($db->nextRecord())
	$count_real_last_active = $db->getField('count');
if(SmrSession::$last_accessed < TIME - 600)
	++$count_real_last_active;


$db->query('SELECT * FROM player
		WHERE last_cpl_action >= ' . $db->escapeNumber(TIME - 600) . '
			AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
		ORDER BY experience DESC, player_name DESC');
$count_last_active = $db->getNumRows();

// fix it if some1 is using the logoff button
if ($count_real_last_active < $count_last_active)
	$count_real_last_active = $count_last_active;

// Get the summary text
$summary = 'There ';
if ($count_real_last_active != 1) {
	$summary .= 'are '.$count_real_last_active.' players who have ';
} else {
	$summary .= 'is 1 player who has ';
}
$summary .= 'accessed the server in the last 10 minutes.<br />';

if ($count_last_active == 0) {
	$summary .= 'No one was moving so your ship computer can\'t intercept any transmissions.';
} else {
	if ($count_last_active == $count_real_last_active) {
		$summary .= 'All ';
	} else {
		$summary .= 'A few ';
	}
	$summary .= 'of them were moving so your ship computer was able to intercept '.$count_last_active.' '.pluralise('transmission', $count_last_active).'.';
}

$summary .= '<br />The traders listed in <span class="italic">italics</span> are still ranked as Newbie or Beginner.';

$template->assign('Summary', $summary);

$allRows = array();
if ($count_last_active > 0) {
	$db2 = new SmrMySqlDatabase();
	while ($db->nextRecord()) {
		$row = array();

		$curr_player = SmrPlayer::getPlayer($db->getInt('account_id'), $player->getGameID(), false, $db);
		$row['player'] = $curr_player;

		// How should we style the row for this player?
		$class='';
		if ($player->equals($curr_player))
			$class .= 'bold';
		if ($curr_player->hasNewbieStatus())
			$class.= ' newbie';
		if ($class!='')
			$class = ' class="'.trim($class).'"';
		$row['tr_class'] = $class;

		// What should the player name be displayed as?
		$container = create_container('skeleton.php', 'trader_search_result.php');
		$container['player_id']	= $curr_player->getPlayerID();
		$name = $curr_player->getLevelName() . ' ' . $curr_player->getDisplayName();
		$db2->query('SELECT * FROM cpl_tag WHERE account_id = ' . $db2->escapeNumber($curr_player->getAccountID()) . ' ORDER BY custom DESC');
		while ($db2->nextRecord()) {
			if (!empty($db2->getField('custom_rank'))) {
				$name = $db2->getField('custom_rank') . ' ' . $curr_player->getDisplayName();
			}
			if (!empty($db2->getField('tag'))) {
				$name .= ' ' . $db2->getField('tag');
			}
		}
		$row['name_link'] = create_link($container, $name);

		$allRows[] = $row;
	}
}

$template->assign('AllRows', $allRows);
$template->assign('ThisPlayer', $player);
