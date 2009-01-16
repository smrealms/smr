<?php

function player_joined_game($account_id, $game_id) {

	// new db object
	$db = new SmrMySqlDatabase();

	// check if there is an entry in the player table
	$db->query('SELECT account_id
				FROM player
				WHERE account_id = '.$account_id.' AND
					  game_id = '.$game_id);
	if ($db->getNumRows())
		return true;
	else
		return false;

}

function join_game($account_id, $game_id, $player_name = '', $race_id = 0) {

	if ($race_id == 0)
		$race_id = mt_rand(2, 9);

	if (empty($player_name)) {

		$name_list = array('Fry', 'Bender', 'Leela', 'Professor', 'Zoidberg', 'Amy', 'Hermes', 'Brannigan', 'Kif', 'Nibbler');

		$player_name = $name_list[mt_rand(0, count($name_list) - 1)];

	}

	$account = new SMR_ACCOUNT();
	$account->get_by_id($account_id);

	// new db object
	$db = new SmrMySqlDatabase();

	$db->query('SELECT account_id
				FROM player
				WHERE game_id = '.$game_id.' AND
					  player_name = '.$db->escapeString($player_name).'
			   ');
	if ($db->getNumRows() > 0)
		log_message('.$db->escapeString($player_name already joined that game.', ERROR);

	// check if hof entry is there
	$db->query('SELECT account_id
				FROM account_has_stats
				WHERE account_id = '.$account->account_id.'
			   ');
	if (!$db->getNumRows())
		$db->query('INSERT INTO account_has_stats
					(account_id, HoF_name)
					VALUES ($account->account_id, '.$db->escapeString($player_name).')
				   ');

	// put him in a sector with a hq
	$hq_id = $race_id + 101;
	$db->query('SELECT *
				FROM location NATURAL JOIN sector
				WHERE location.game_id = '.$game_id.' AND
					  location_type_id = $hq_id
			   ');
	if ($db->nextRecord())
		$home_sector_id = $db->getField('sector_id');
	else
		$home_sector_id = 1;

	// get rank_id
	$rank_id = $account->get_rank();

	// for newbie and beginner another ship, more shields and armor
	if ($rank_id < 3 && $account->veteran == 'FALSE') {

		$ship_id = 28;
		$amount_shields = 75;
		$amount_armor = 150;

	} else {

		$ship_id = 1;
		$amount_shields = 50;
		$amount_armor = 50;

	}

	// get start time
	$db->query('SELECT *
				FROM game
				WHERE game_id = '.$game_id.'
			   ');
	if ($db->nextRecord())
		$start_date	= $db->getField('start_date');
	else
		log_message($account_id, 'Game not found!', ERROR);

	// get the time since game start (but max 24h)
	$time_since_start = time() - strtotime($start_date);
	if ($time_since_start > 86400)
		$time_since_start = 86400;

	// credit him this time
	$last_turn_update = time() - $time_since_start;

	$db->lockTable('player');

	// get last registered player id in that game and increase by one.
	$db->query('SELECT MAX(player_id)
				FROM player
				WHERE game_id = '.$game_id.'
			   ');
	if ($db->nextRecord())
		$player_id = $db->getField('MAX(player_id)') + 1;
	else
		$player_id = 1;

	// insert into player table.
	$db->query('INSERT INTO player
				(account_id, game_id, player_id, player_name, race_id, ship_type_id, sector_id, last_turn_update, last_active)
				VALUES('.$account->account_id.', '.$game_id.', '.$player_id.', '.$db->escapeString($player_name).', '.$race_id.', '.$ship_id.', '.$home_sector_id.', '.$last_turn_update.', ' . time() . ')
			   ');

	$db->unlock();

	// give the player shields
	$db->query('INSERT INTO ship_has_hardware
				(account_id, game_id, hardware_type_id, amount, old_amount)
				VALUES('.$account->account_id.', '.$game_id.', 1, '.$amount_shields.', '.$amount_shields.')
			   ');

	// give the player armor
	$db->query('INSERT INTO ship_has_hardware
				(account_id, game_id, hardware_type_id, amount, old_amount)
				VALUES('.$account->account_id.', '.$game_id.', 2, '.$amount_armor.', '.$amount_armor.')
			   ');

	// give the player cargo hold
	$db->query('INSERT INTO ship_has_hardware
				(account_id, game_id, hardware_type_id, amount, old_amount)
				VALUES('.$account->account_id.', '.$game_id.', 3, 40, 40)
			   ');

	// give the player weapons
	$db->query('INSERT INTO ship_has_weapon
				(account_id, game_id, order_id, weapon_type_id)
				VALUES('.$account->account_id.', '.$game_id.', 1, 46)
			   ');

	// update stats
	$db->query('UPDATE account_has_stats
				SET games_joined = games_joined + 1
				WHERE account_id = '.$account->account_id.'
			   ');

	// insert the huge amount of sectors into the database :)
	$db->query('SELECT MIN(sector_id), MAX(sector_id)
				FROM sector
				WHERE game_id = '.$game_id.'
			   ');
	if (!$db->nextRecord())
		log_message($account_id, 'This game doesn\'t have any sectors', ERROR);

	$min_sector = $db->getField('MIN(sector_id)');
	$max_sector = $db->getField('MAX(sector_id)');

	for ($i = $min_sector; $i <= $max_sector; $i++)
	{

	    //if this is our home sector we dont add it.
	    if ($i == $home_sector_id)
	        continue;

	    $db->query('INSERT INTO player_visited_sector
	    			(account_id, game_id, sector_id)
	    			VALUES ('.$account->account_id.', '.$game_id.', '.$i.')');

	}


}

define('NEWBIE', 1);
define('BEGINNER', 2);
define('FLEDGLING', 3);
define('AVERAGE', 4);

function different_level($rank1, $rank2, $forced_vet1, $forced_vet2) {

	// we are newbie, he vet
	if ($rank1 < FLEDGLING && $forced_vet1 == 'FALSE' && ($rank2 > BEGINNER || $forced_vet2 == 'TRUE'))
		return true;

	// we are vet, he newbie
	if (($rank1 > BEGINNER || $forced_vet1 == 'TRUE') && $rank2 < FLEDGLING && $forced_vet2 == 'FALSE')
		return true;

	return false;

}

function get_colored_text($value, $text) {

	if ($value <= -300)
		$color = 'red';
	elseif ($value >= 300)
		$color = 'green';
	else
		$color = 'yellow';

	return '<span style="color:'.$color.';">'.$text.'</span>';

}

?>