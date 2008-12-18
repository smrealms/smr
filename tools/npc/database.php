<?php

function get_sector($account_id, $game_id, $column) {

	// new db object
	$db = new SMR_DB();

	$db->query('SELECT $column
				FROM sector
				WHERE game_id = '.$game_id.'
			   ');
	if ($db->next_record())
		return $db->f($column);
	else
		log_message($account_id, 'Column $column for this sector not found', ERROR);

}

function get_player($account_id, $game_id, $column) {

	// new db object
	$db = new SMR_DB();

	$db->query('SELECT $column
				FROM player
				WHERE account_id = '.$account_id.' AND
					  game_id = '.$game_id.'
			   ');

	if ($db->next_record())
		return $db->f($column);
	else
		log_message($account_id, 'Column $column for this player not found', ERROR);

}

function set_player($account_id, $game_id, $column, $value) {

	// new db object
	$db = new SMR_DB();

	$db->query('UPDATE player
				SET $column = '.$db->escapeString($value'
				WHERE account_id = '.$account_id.' AND
					  game_id = '.$game_id.'
			   ');

}

function get_account($account_id, $column) {

	// new db object
	$db = new SMR_DB();

	$db->query('SELECT $column
				FROM account
				WHERE account_id = '.$account_id.'
			   ');

	if ($db->next_record())
		return $db->f($column);
	else
		log_message($account_id, 'Column $column for this account not found', ERROR);

}

function get_ship($account_id, $game_id, $column) {

	// new db object
	$db = new SMR_DB();

	$db->query('SELECT $column
				FROM player, ship_type
				WHERE player.ship_type_id = ship_type.ship_type_id AND
					  player.account_id = '.$account_id.' AND
					  player.game_id = '.$game_id.'
			   ');

	if ($db->next_record())
		return $db->f($column);
	else
		log_message($account_id, 'Column $column for this ship not found', ERROR);

}

function get_game($game_id, $column) {
	$account_id=1;
	// new db object
	$db = new SMR_DB();

	$db->query('SELECT $column
				FROM game
				WHERE game_id = '.$game_id.'
			   ');

	if ($db->next_record())
		return $db->f($column);
	else
		log_message($account_id, 'Column $column for this game not found', ERROR);

}

function set_stats($account_id, $column, $value) {

	// new db object
	$db = new SMR_DB();

	$db->query('UPDATE account_has_stats
				SET $column = $column + '.$db->escapeString($value'
				WHERE account_id = '.$account_id.'
			   ');

}


?>