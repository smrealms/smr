<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Current Players');
$db = Smr\Database::getInstance();
$db->write('DELETE FROM cpl_tag WHERE expires > 0 AND expires < ' . $db->escapeNumber(Smr\Epoch::time()));
$dbResult = $db->read('SELECT count(*) count FROM active_session
			WHERE last_accessed >= ' . $db->escapeNumber(Smr\Epoch::time() - 600) . ' AND
				game_id = ' . $db->escapeNumber($player->getGameID()));
$count_real_last_active = 0;
if ($dbResult->hasRecord()) {
	$count_real_last_active = $dbResult->record()->getInt('count');
}
if ($session->getLastAccessed() < Smr\Epoch::time() - 600) {
	++$count_real_last_active;
}


$dbResult = $db->read('SELECT * FROM player
		WHERE last_cpl_action >= ' . $db->escapeNumber(Smr\Epoch::time() - 600) . '
			AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
		ORDER BY experience DESC, player_name DESC');
$count_last_active = $dbResult->getNumRecords();

// fix it if some1 is using the logoff button
if ($count_real_last_active < $count_last_active) {
	$count_real_last_active = $count_last_active;
}

// Get the summary text
$summary = 'There ';
if ($count_real_last_active != 1) {
	$summary .= 'are ' . $count_real_last_active . ' players who have ';
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
	$summary .= 'of them were moving so your ship computer was able to intercept ' . pluralise($count_last_active, 'transmission') . '.';
}

$summary .= '<br />The traders listed in <span class="italic">italics</span> are still ranked as Newbie or Beginner.';

$template->assign('Summary', $summary);

$allRows = [];
foreach ($dbResult->records() as $dbRecord) {
	$row = [];

	$curr_player = SmrPlayer::getPlayer($dbRecord->getInt('account_id'), $player->getGameID(), false, $dbRecord);
	$row['player'] = $curr_player;

	// How should we style the row for this player?
	$class = '';
	if ($player->equals($curr_player)) {
		$class .= 'bold';
	}
	if ($curr_player->hasNewbieStatus()) {
		$class .= ' newbie';
	}
	if ($class != '') {
		$class = ' class="' . trim($class) . '"';
	}
	$row['tr_class'] = $class;

	// What should the player name be displayed as?
	$container = Page::create('trader_search_result.php');
	$container['player_id'] = $curr_player->getPlayerID();
	$name = $curr_player->getLevelName() . ' ' . $curr_player->getDisplayName();
	$dbResult2 = $db->read('SELECT * FROM cpl_tag WHERE account_id = ' . $db->escapeNumber($curr_player->getAccountID()) . ' ORDER BY custom DESC');
	foreach ($dbResult2->records() as $dbRecord2) {
		if (!empty($dbRecord2->getField('custom_rank'))) {
			$name = $dbRecord2->getField('custom_rank') . ' ' . $curr_player->getDisplayName();
		}
		if (!empty($dbRecord2->getField('tag'))) {
			$name .= ' ' . $dbRecord2->getField('tag');
		}
	}
	$row['name_link'] = create_link($container, $name);

	$allRows[] = $row;
}

$template->assign('AllRows', $allRows);
