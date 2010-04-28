#!/usr/bin/php -q
<?php

mt_srand((double)microtime()*1000000);

// config file
include( realpath(dirname(__FILE__)) . '/../htdocs/config.inc');

include(LIB . 'Default/SmrMySqlDatabase.class.inc');
include(LIB . 'smr_account.inc');

include('npc/logger.php');
include('npc/game_handling.php');
include('npc/scout.php');
include('npc/player.php');
include('npc/goal.php');
include('npc/moving.php');
include('npc/database.php');

// new db object
$db = new SmrMySqlDatabase();

while (true) {

	$db->query('SELECT account_id, game_id
				FROM npc
				WHERE next_active < ' . time() . ' AND
					  active = \'TRUE\'
				ORDER BY next_active
				LIMIT 1
			   ');
	if (!$db->nextRecord()) {

		sleep(1);
		continue;

	}

	// the game we going to play
	$account_id	= $db->getField('account_id');
	$game_id	= $db->getField('game_id');

	// check if the player joined that game.
	if (!player_joined_game($account_id, $game_id))
		join_game($account_id, $game_id);

	// refresh this users turns
	updateTurns($account_id, $game_id);

	// no turns? wait a minute for new
	if (get_player($account_id, $game_id, 'turns') == 0) {

		log_message($account_id, 'I\'m low on turns. waiting for new one.');

		set_npc_sleep($account_id, $game_id, 60);
		continue;

	}

	if ($short_term_goal = get_short_term_goal($account_id, $game_id)) {

		if ($short_term_goal['type'] == 'follow_course') {

			$new_course = follow_course($account_id, $game_id, $short_term_goal['task']);

			if (!empty($new_course))
				set_short_term_goal($account_id, $game_id, 'follow_course', $new_course);
			else
				delete_short_term_goal($account_id, $game_id);

		}

	} elseif ($long_term_goal = get_long_term_goal($account_id, $game_id)) {

		if ($long_term_goal['type'] == 'scout') {

			scout($account_id, $game_id);

		}

	}

	set_npc_sleep($account_id, $game_id, 3);

}

?>