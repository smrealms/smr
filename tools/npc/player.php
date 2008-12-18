<?php

function updateTurns($account_id, $game_id) {

	// get a view infos first
	$last_turn_update	= get_player($account_id, $game_id, 'last_turn_update');
	$turns				= get_player($account_id, $game_id, 'turns');
	$ship_speed			= get_ship($account_id, $game_id, 'speed');
	$game_speed			= get_game($game_id, 'game_speed');

	// update turns?
	$time_diff = time() - $last_turn_update;

	// how many turns would he get right now?
	$new_turns = floor($time_diff * $ship_speed * $game_speed / 3600);

	// do we have at least one turn to give?
	if ($new_turns > 0) {

		// recalc the time to avoid errors
		$last_turn_update += ceil($new_turns * 3600 / $ship_speed / $game_speed);

		// max turns are dependent on game speed
		$max_turns = 400 * $game_speed;

		// do we have more than (400 * $game_speed) turns?
		if ($turns + $new_turns > $max_turns) {

			// don't get more than $max_turns turns
			$new_turns = $max_turns - $turns;

			// but don't take turns away
			if ($new_turns < 0)
				$new_turns = 0;

		}

		// add the new turns to our current turns
		$turns += $new_turns;

		// save turns to db
		set_player($account_id, $game_id, 'turns', $turns);
		set_player($account_id, $game_id, 'last_turn_update', $last_turn_update);

		log_message($account_id, 'I received $new_turns turns and have $turns left.');

	}

}

function get_colored_name($account_id, $game_id) {

	return get_colored_text(get_player($account_id, $game_id, 'alignment'),
							get_player($account_id, $game_id, 'player_name') . '&nbsp;(' . get_player($account_id, $game_id, 'player_id') . ')'
						   );

}

// message types
define('GLOBALMSG', 1);
define('PLAYERMSG', 2);
define('PLANETMSG', 3);
define('SCOUTMSG', 4);
define('POLITICALMSG', 5);

function send_message($sender_id, $game_id, $receiver_id, $message_type_id, $message) {

	// send him the message
	$db->query('INSERT INTO message
				(account_id, game_id, message_type_id, message_text, sender_id, send_time)
				VALUES($receiver_id, $game_id, $message_type_id, $message, $sender_id, ' . time() . ')
			   ');

	// give him the message icon
	$db->query('REPLACE INTO player_has_unread_messages
				(game_id, account_id, message_type_id) VALUES
				($game_id, $receiver_id, $message_type_id)
			   ');

}

function takeTurns($account_id, $game_id, $number) {

	// take turns
	$turns = get_player($account_id, $game_id, 'turns') - $number;
	set_player($account_id, $game_id, 'turns', $turns);

	$newbie_turns = get_player($account_id, $game_id, 'newbie_turns');

	// take newbie turns (if we have)
	if ($newbie_turns > 0) {

		$newbie_turns -= $number;
		if ($newbie_turns < 0)
			$newbie_turns = 0;

		set_player($account_id, $game_id, 'newbie_turns', $newbie_turns);

	}

	set_player($account_id, $game_id, 'last_active', time());
	set_stats($account_id, 'turns_used', $number);

}



?>