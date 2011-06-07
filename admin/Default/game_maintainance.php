<?php

//script to do all the cleanup maintainance stuff...
		
///////////////////////////////////////////
//
// close game and (lock) clear active
//
///////////////////////////////////////////

$db->query('INSERT INTO game_disable (reason) VALUES (\'Daily Maintenance...check back in 10 minutes\')');
$db->lockTable('active_session');
$db->query('DELETE FROM active_session');
$db->unlock();

///////////////////////////////////////////
//
// optimize tables
//
///////////////////////////////////////////

$db->query('OPTIMIZE TABLE `account` , `account_donated` , `account_exceptions` , `account_has_closing_history` , `account_has_credits` , `account_has_ip` , `account_has_logs` , `account_has_permission` , `account_has_points` , `account_has_stats` , `account_is_closed` , `active_session` , `album` , `album_has_comments` , `alliance` , `alliance_bank_transactions` , `alliance_has_roles` , `alliance_thread` , `alliance_thread_topic` , `announcement` , `anon_bank` , `anon_bank_transactions` , `bar_drink` , `bar_tender` , `bar_wall` , `beta_test` , `bounty` , `changelog` , `closing_reason` , `galactic_post_applications` , `galactic_post_article` , `galactic_post_online` , `galactic_post_paper` , `galactic_post_paper_content` , `galactic_post_writer` , `galaxy` , `game` , `game_galaxy`, `game_disable` , `good` , `hardware_type` , `irc_seen` , `kills` , `level` , `location` , `location_is_bank` , `location_is_bar` , `location_is_fed` , `location_is_hq` , `location_is_ug` , `location_sells_hardware` , `location_sells_ships` , `location_sells_weapons` , `location_type` , `log_has_notes` , `log_type` , `manual` , `mb_exceptions` , `mb_keywords` , `message` , `message_notify` , `message_type` , `multi_checking` , `multi_checking_cookie` , `news` , `newsletter` , `notification` , `npc` , `npc_long_term_goal` , `npc_short_term_goal` , `permission` , `planet` , `planet_attack` , `planet_is_building` , `planet_construction` , `planet_cost_credits` , `planet_cost_good` , `planet_cost_time` , `planet_has_cargo` , `planet_has_building` , `player` , `player_has_alliance_role` , `player_has_drinks` , `player_has_relation` , `player_has_stats` , `player_has_ticker` , `player_has_ticket` , `player_has_unread_messages` , `player_is_president` , `player_plotted_course` , `player_read_thread` , `player_visited_port` , `player_visited_sector` , `player_votes_pact` , `player_votes_relation` , `plot_cache` , `port` , `port_attack_times` , `port_has_goods` , `profile` , `race` , `race_has_relation` , `race_has_voting` , `rankings` , `sector` , `sector_has_forces` , `ship_has_cargo` , `ship_has_hardware` , `ship_has_illusion` , `ship_has_name` , `ship_has_weapon` , `ship_is_cloaked` , `ship_type` , `ship_type_support_hardware` , `version` , `warp` , `weapon_type`');

///////////////////////////////////////////
//
// backup db
//
///////////////////////////////////////////

$file_name = ROOT . 'documentation/SmrMySqlDatabase_BACKUP_' . date('m-d') . '.sql';
$db2 = new SmrMySqlDatabase();
if (!file_exists($file_name)) {
	
	//we are good to create!
	if (touch($file_name)) {
		
		if (!$db_file = fopen($file_name, 'a')) {
			
			echo 'Cannot open file ('.$file_name.')';
			exit;
			
		}

		//we are good to add entries
		$db->query('SHOW TABLES');
		$db2 = new SmrMySqlDatabase();
		while ($db->nextRecord()) {
			
			$table = $db->getField(0);
			$db2->query('SHOW COLUMNS FROM '.$table);
			$insert = 'INSERT INTO '.$table.' (';
			$i = $db2->getNumRows() - 1;
			$cols = $db2->getNumRows() -1;
			while ($db2->nextRecord()) {
				
				$field = $db2->getField(0);
				$insert .= $field;
				if ($i != 0) $insert .= ',';
				$i--;
			
			}
			$i = $cols;
			$insert .= ') VALUES (';
			$db2->query('SELECT * FROM '.$table);
			while ($db2->nextRecord()) {
				
				$db_ent = $insert;
				for ($j=0; $j<=$cols; $j++) {
					
					$db_ent .= $db2->getField($j);
					if ($i != 0) $db_ent .= ',';
					$i--;
				
				}
				$db_ent .= ');';
				if (fwrite($db_file, $db_ent) === FALSE) {
					
					echo 'Cannot write to file ('.$file_name.')';
					exit;
					
				}
				
			}
			
		}
		fclose($db_file);
		
	}
	
}

///////////////////////////////////////////
//
// reopen game
//
///////////////////////////////////////////

///////////////////////////////////////////
//
// message table stuff
//
///////////////////////////////////////////
	

?>