<?php

$action = $_REQUEST['action'];
if ($action == 'End >>') {
	$container = create_container('skeleton.php', 'universe_create_end.php');
	$container['game_id'] = $var['game_id'];
	forward($container);
}

// check if no account was selected
if ($_POST['admin_id'] == 0)
	create_error('No Account selected!');

if (empty($_POST['player_name']))
	create_error('You must select a name for that player!');

// create an account object from the guy
$admin_account =& SmrAccount::getAccount($_POST['admin_id']);

// check if hof entry is there
$db->query('SELECT * FROM account_has_stats WHERE account_id = '.$admin_account->getAccountID());
if (!$db->getNumRows())
	$db->query('INSERT INTO account_has_stats (account_id, HoF_name) VALUES ('.$admin_account->getAccountID().', ' . $db->escape_string($admin_account->getLogin(), true) . ')');

// give game stats entry
$db->query('INSERT INTO player_has_stats (account_id, game_id) VALUES ('.$admin_account->getAccountID().', ' . $var['game_id'] . ')');

// put him in a sector with a hq
$hq_id = $_POST['race_id'] + 101;
$db->query('SELECT * FROM location JOIN sector USING(game_id, sector_id) ' .
		   'WHERE game_id = ' . $var['game_id'] . ' AND ' .
		   'location_type_id = '.$hq_id);
if ($db->nextRecord())
	$home_sector_id = $db->getField('sector_id');
else
	$home_sector_id = 1;

// get rank_id
$rank_id = $admin_account->getRank();

// for newbie and beginner another ship, more shields and armour
if ($admin_account->isNewbie()) {
	$ship_id = 28;
	$amount_shields = 75;
	$amount_armour = 150;

} else {

	$ship_id = 1;
	$amount_shields = 50;
	$amount_armour = 50;

}

// get the time since game start (but max 24h)
$time_since_start = TIME - strtotime($start_date);
if ($time_since_start > 86400)
	$time_since_start = 86400;

// credit him this time
$last_turn_update = TIME - $time_since_start;

$db->lockTable('player');

// get last registered player id in that game and increase by one.
$db->query('SELECT MAX(player_id) FROM player WHERE game_id = ' . $var['game_id'] . ' ORDER BY player_id DESC LIMIT 1');
if ($db->nextRecord())
	$player_id = $db->getField('MAX(player_id)') + 1;
else
	$player_id = 1;

// insert into player table.
// Newbie Help Leader goes into Newbie Help alliance (#302)
if($_POST['admin_id'] != ACCOUNT_ID_NHL) {
	$alliance_id = 1;
}
else {
	$alliance_id = 302;
}

$db->query('INSERT INTO player (account_id, game_id, player_id, player_name, race_id, ship_type_id, sector_id, last_turn_update, last_cpl_action, last_active, alliance_id) ' .
						'VALUES(' . $_POST['admin_id'] . ', ' . $var['game_id'] . ', '.$player_id.', ' . $db->escape_string($_POST['player_name'], true) . ', ' . $_POST['race_id'] . ', '.$ship_id.', '.$home_sector_id.', '.$last_turn_update.', ' . TIME.', ' . TIME . ','.$alliance_id.')');

$db->unlock();

// give the player shields
$db->query('INSERT INTO ship_has_hardware (account_id, game_id, hardware_type_id, amount, old_amount) ' .
								   'VALUES(' . $_POST['admin_id'] . ', ' . $var['game_id'] . ', 1, '.$amount_shields.', '.$amount_shields.')');
// give the player armour
$db->query('INSERT INTO ship_has_hardware (account_id, game_id, hardware_type_id, amount, old_amount) ' .
								   'VALUES(' . $_POST['admin_id'] . ', ' . $var['game_id'] . ', 2, '.$amount_armour.', '.$amount_armour.')');
// give the player cargo hold
$db->query('INSERT INTO ship_has_hardware (account_id, game_id, hardware_type_id, amount, old_amount) ' .
								   'VALUES(' . $_POST['admin_id'] . ', ' . $var['game_id'] . ', 3, 40, 40)');
// give the player weapons
$db->query('INSERT INTO ship_has_weapon (account_id, game_id, order_id, weapon_type_id) ' .
								 'VALUES(' . $_POST['admin_id'] . ', ' . $var['game_id'] . ', 1, 46)');

// update stats
$db->query('UPDATE account_has_stats SET games_joined = games_joined + 1 WHERE account_id = ' . $_POST['admin_id']);

// insert the huge amount of sectors into the database :)
$db->query('SELECT MIN(sector_id), MAX(sector_id)
			FROM sector
			WHERE game_id = ' . $var['game_id']);
if (!$db->nextRecord())
	create_error('This game doesn\'t have any sectors!');

$min_sector = $db->getField('MIN(sector_id)');
$max_sector = $db->getField('MAX(sector_id)');

for ($i = $min_sector; $i <= $max_sector; $i++) {

	//if this is our home sector we dont add it.
	if ($i == $home_sector_id) {
		continue;
	}

	$db->query('INSERT INTO player_visited_sector (account_id, game_id, sector_id) VALUES (' . $_POST['admin_id'] . ', ' . $var['game_id'] . ', '.$i.')');

}

$container = array();
$container['url']		= 'skeleton.php';
$container['body']		= 'universe_create_admin.php';
$container['game_id']	= $var['game_id'];
forward($container);

?>