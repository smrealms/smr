<?php declare(strict_types=1);

$account_id = Request::getInt('account_id');
$player_name = Request::get('player_name');
$searchGameID = Request::getInt('game_id');

if (!empty($player_name)) {
	$gameIDClause = $searchGameID != 0 ? ' AND game_id = ' . $db->escapeNumber($var['SearchGameID']) . ' ' : '';
	$db->query('SELECT account_id FROM player
					WHERE player_name = ' . $db->escapeString($player_name) . $gameIDClause . '
					ORDER BY game_id DESC LIMIT 1');
	if ($db->nextRecord()) {
		$account_id = $db->getInt('account_id');
	} else {
		$db->query('SELECT * FROM player
						WHERE player_name LIKE ' . $db->escapeString($player_name . '%') . $gameIDClause);
		if ($db->nextRecord()) {
			$account_id = $db->getInt('account_id');
		}
	}
}

// get account from db
$db->query('SELECT account_id FROM account WHERE account_id = ' . $db->escapeNumber($account_id) . ' OR ' .
									   'login LIKE ' . $db->escapeString(Request::get('login')) . ' OR ' .
									   'email LIKE ' . $db->escapeString(Request::get('email')) . ' OR ' .
									   'hof_name LIKE ' . $db->escapeString(Request::get('hofname')) . ' OR ' .
									   'validation_code LIKE ' . $db->escapeString(Request::get('val_code')));
if ($db->nextRecord()) {
	$container = create_container('skeleton.php', 'account_edit.php');
	$container['account_id'] = $db->getInt('account_id');
} else {
	$container = create_container('skeleton.php', 'account_edit_search.php');
	$container['errorMsg'] = 'No matching accounts were found!';
}
forward($container);
