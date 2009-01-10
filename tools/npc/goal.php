<?php

// INSERT INTO `npc_short_term_goal` VALUES (500, 27, 'follow_course', '50-49-48-33-18-17-16-1');

function get_short_term_goal($account_id, $game_id) {

	// new db object
	$db = new SmrMySqlDatabase();

	$db->query('SELECT *
				FROM npc_short_term_goal
				WHERE account_id = '.$account_id.' AND
					  game_id = '.$game_id.'
			   ');
	if ($db->next_record())
		return array('type' => $db->f('type'), 'task' => $db->f('task'));
	else
		return false;

}

function set_short_term_goal($account_id, $game_id, $type, $task) {

	// new db object
	$db = new SmrMySqlDatabase();

	$db->query('REPLACE INTO npc_short_term_goal
				(account_id, game_id, type, task)
				VALUES ($account_id, $game_id, '.$db->escapeString($type).', '.$db->escapeString($task).')
			   ');

}

function delete_short_term_goal($account_id, $game_id) {

	// new db object
	$db = new SmrMySqlDatabase();

	$db->query('DELETE FROM npc_short_term_goal
				WHERE account_id = '.$account_id.' AND
					  game_id = '.$game_id.'
			   ');

}

function get_long_term_goal($account_id, $game_id) {

	// new db object
	$db = new SmrMySqlDatabase();

	$db->query('SELECT *
				FROM npc_long_term_goal
				WHERE account_id = '.$account_id.' AND
					  game_id = '.$game_id.'
			   ');
	if ($db->next_record())
		return array('type' => $db->f('type'), 'task' => $db->f('task'));
	else
		return false;

}

function set_long_term_goal($account_id, $game_id, $type, $task) {

	// new db object
	$db = new SmrMySqlDatabase();

	$db->query('REPLACE INTO npc_long_term_goal
				(account_id, game_id, type, task)
				VALUES ($account_id, $game_id, '.$db->escapeString($type).', '.$db->escapeString($task).')
			   ');

}

function delete_long_term_goal($account_id, $game_id) {

	// new db object
	$db = new SmrMySqlDatabase();

	$db->query('DELETE FROM npc_long_term_goal
				WHERE account_id = '.$account_id.' AND
					  game_id = '.$game_id.'
			   ');

}

function set_npc_sleep($account_id, $game_id, $time) {

	// new db object
	$db = new SmrMySqlDatabase();

	$time += time();

	$db->query('UPDATE npc
				SET next_active = $time
				WHERE account_id = '.$account_id.' AND
					  game_id = '.$game_id.'
			   ');

}

?>