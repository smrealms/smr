-- Narrow identifier and game-state columns while preserving their existing
-- signedness, nullability, defaults, and auto-increment behaviour.

ALTER TABLE `account_exceptions`
	MODIFY `account_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `account_has_permission`
	MODIFY `account_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `permission_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `account_has_points`
	MODIFY `account_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `account_is_closed`
	MODIFY `account_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `account_shares_info`
	MODIFY `from_account_id` smallint unsigned NOT NULL,
	MODIFY `to_account_id` smallint unsigned NOT NULL,
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `account_votes_for_feature`
	MODIFY `account_id` smallint unsigned NOT NULL;

ALTER TABLE `alliance`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `alliance_kills` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `alliance_deaths` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `alliance_bank_transactions`
	MODIFY `alliance_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `alliance_invites_player`
	MODIFY `game_id` smallint unsigned NOT NULL;
ALTER TABLE `alliance_thread_topic`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `alliance_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `thread_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `alliance_vs_alliance`
	MODIFY `game_id` smallint NOT NULL DEFAULT '0',
	MODIFY `alliance_id_1` smallint NOT NULL DEFAULT '0',
	MODIFY `alliance_id_2` smallint NOT NULL DEFAULT '0',
	MODIFY `kills` smallint NOT NULL DEFAULT '0';
ALTER TABLE `anon_bank`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `anon_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `anon_bank_transactions`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `anon_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `transaction_id` smallint unsigned NOT NULL DEFAULT '0';

ALTER TABLE `bar_tender`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `bounty`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `chess_game`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `chess_game_moves`
	MODIFY `move_id` smallint unsigned NOT NULL AUTO_INCREMENT,
	MODIFY `piece_id` tinyint unsigned NOT NULL,
	MODIFY `start_x` tinyint unsigned NOT NULL,
	MODIFY `start_y` tinyint unsigned NOT NULL,
	MODIFY `end_x` tinyint unsigned NOT NULL,
	MODIFY `end_y` tinyint unsigned NOT NULL,
	MODIFY `piece_taken` tinyint NULL DEFAULT NULL,
	MODIFY `promote_piece_id` tinyint unsigned NULL DEFAULT NULL;
ALTER TABLE `cpl_tag`
	MODIFY `account_id` smallint NOT NULL DEFAULT '0';

ALTER TABLE `debug`
	MODIFY `account_id` smallint unsigned NOT NULL;
ALTER TABLE `draft_history`
	MODIFY `game_id` smallint unsigned NOT NULL;
ALTER TABLE `draft_leaders`
	MODIFY `game_id` smallint unsigned NOT NULL,
	MODIFY `home_sector_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `feature_request_comments`
	MODIFY `comment_id` smallint unsigned NOT NULL AUTO_INCREMENT,
	MODIFY `poster_id` smallint unsigned NOT NULL;

ALTER TABLE `galactic_post_applications`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `galactic_post_article`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `galactic_post_paper`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `galactic_post_paper_content`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `galactic_post_writer`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `game`
	MODIFY `game_id` smallint unsigned NOT NULL AUTO_INCREMENT,
	MODIFY `max_turns` mediumint unsigned NOT NULL,
	MODIFY `start_turns` mediumint unsigned NOT NULL DEFAULT '15',
	MODIFY `max_players` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `game_galaxy`
	MODIFY `game_id` smallint unsigned NOT NULL,
	MODIFY `width` smallint unsigned NOT NULL,
	MODIFY `height` smallint unsigned NOT NULL;
ALTER TABLE `good`
	MODIFY `good_id` tinyint unsigned NOT NULL AUTO_INCREMENT,
	MODIFY `base_price` smallint unsigned NOT NULL,
	MODIFY `max_amount` smallint unsigned NOT NULL DEFAULT '5000',
	MODIFY `good_class` tinyint unsigned NOT NULL DEFAULT '1';

ALTER TABLE `hardware_type`
	MODIFY `hardware_type_id` tinyint unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `level`
	MODIFY `level_id` tinyint unsigned NOT NULL AUTO_INCREMENT,
	MODIFY `requirement` mediumint unsigned NOT NULL;
ALTER TABLE `location`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `sector_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `location_type_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `location_is_bank`
	MODIFY `location_type_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `location_is_bar`
	MODIFY `location_type_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `location_is_fed`
	MODIFY `location_type_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `location_is_hq`
	MODIFY `location_type_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `location_is_ug`
	MODIFY `location_type_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `location_sells_hardware`
	MODIFY `location_type_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `hardware_type_id` tinyint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `location_sells_ships`
	MODIFY `location_type_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `ship_type_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `location_sells_special`
	MODIFY `game_id` smallint unsigned NOT NULL,
	MODIFY `sector_id` smallint unsigned NOT NULL,
	MODIFY `location_type_id` smallint unsigned NOT NULL,
	MODIFY `weapon_type_id` smallint unsigned NOT NULL;
ALTER TABLE `location_sells_weapons`
	MODIFY `location_type_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `weapon_type_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `location_type`
	MODIFY `location_type_id` smallint unsigned NOT NULL AUTO_INCREMENT;

ALTER TABLE `message_boxes`
	MODIFY `game_id` smallint unsigned NOT NULL,
	MODIFY `sender_id` smallint unsigned NOT NULL;
ALTER TABLE `message_notify`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `multi_checking_cookie`
	MODIFY `account_id` smallint NOT NULL DEFAULT '0';
ALTER TABLE `news`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `newsletter`
	MODIFY `newsletter_id` smallint unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `notification`
	MODIFY `account_id` smallint unsigned NULL DEFAULT NULL;
ALTER TABLE `npc_logs`
	MODIFY `npc_id` smallint unsigned NOT NULL;

ALTER TABLE `planet`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `sector_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `shields` mediumint unsigned NOT NULL DEFAULT '0',
	MODIFY `armour` mediumint unsigned NOT NULL DEFAULT '0',
	MODIFY `drones` mediumint unsigned NOT NULL DEFAULT '0',
	MODIFY `planet_type_id` tinyint unsigned NOT NULL DEFAULT '1';
ALTER TABLE `planet_has_building`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `sector_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `construction_id` tinyint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `planet_has_cargo`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `sector_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `good_id` tinyint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `planet_has_weapon`
	MODIFY `sector_id` smallint unsigned NOT NULL;
ALTER TABLE `planet_is_building`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `sector_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `construction_id` tinyint unsigned NOT NULL DEFAULT '0';

ALTER TABLE `player`
	MODIFY `player_number` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `ship_type_id` smallint unsigned NOT NULL DEFAULT '28',
	MODIFY `turns` mediumint unsigned NOT NULL DEFAULT '0',
	MODIFY `race_id` tinyint unsigned NOT NULL DEFAULT '1',
	MODIFY `newbie_turns` mediumint unsigned NOT NULL DEFAULT '500',
	MODIFY `sector_id` smallint unsigned NOT NULL DEFAULT '1',
	MODIFY `last_sector_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `kills` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `deaths` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `last_port` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `player_attacks_planet`
	MODIFY `game_id` smallint NOT NULL DEFAULT '0',
	MODIFY `sector_id` smallint NOT NULL DEFAULT '0';
ALTER TABLE `player_can_fed`
	MODIFY `game_id` smallint unsigned NOT NULL,
	MODIFY `race_id` tinyint unsigned NOT NULL;
ALTER TABLE `player_has_alliance_role`
	MODIFY `alliance_id` smallint unsigned NOT NULL;
ALTER TABLE `player_has_drinks`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `player_has_mission`
	MODIFY `game_id` smallint unsigned NOT NULL,
	MODIFY `mission_id` smallint unsigned NOT NULL,
	MODIFY `on_step` smallint unsigned NOT NULL;
ALTER TABLE `player_has_relation`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `race_id` tinyint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `player_has_ticker`
	MODIFY `game_id` smallint NOT NULL DEFAULT '0';
ALTER TABLE `player_has_ticket`
	MODIFY `game_id` smallint NOT NULL DEFAULT '0';
ALTER TABLE `player_has_unread_messages`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `player_plotted_course`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `distance` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `player_stored_sector`
	MODIFY `game_id` smallint unsigned NOT NULL,
	MODIFY `sector_id` smallint unsigned NOT NULL;
ALTER TABLE `player_votes_pact`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `race_id_1` tinyint unsigned NOT NULL DEFAULT '0',
	MODIFY `race_id_2` tinyint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `player_votes_relation`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `race_id_1` tinyint unsigned NOT NULL DEFAULT '0',
	MODIFY `race_id_2` tinyint unsigned NOT NULL DEFAULT '0';

ALTER TABLE `port`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `sector_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `level` tinyint unsigned NOT NULL DEFAULT '1',
	MODIFY `race_id` tinyint unsigned NOT NULL DEFAULT '1',
	MODIFY `shields` mediumint unsigned NOT NULL DEFAULT '0',
	MODIFY `armour` mediumint unsigned NOT NULL DEFAULT '0',
	MODIFY `combat_drones` mediumint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `port_has_goods`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `sector_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `good_id` tinyint unsigned NOT NULL DEFAULT '0',
	MODIFY `amount` smallint unsigned NOT NULL;
ALTER TABLE `port_info_cache`
	MODIFY `game_id` smallint unsigned NOT NULL,
	MODIFY `sector_id` smallint unsigned NOT NULL;
ALTER TABLE `profile`
	MODIFY `account_id` smallint unsigned NOT NULL DEFAULT '0';

ALTER TABLE `race_has_relation`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `race_id_1` tinyint unsigned NOT NULL DEFAULT '0',
	MODIFY `race_id_2` tinyint unsigned NOT NULL DEFAULT '0',
	MODIFY `relation` smallint NOT NULL DEFAULT '0';
ALTER TABLE `race_has_voting`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `race_id_1` tinyint unsigned NOT NULL DEFAULT '0',
	MODIFY `race_id_2` tinyint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `route_cache`
	MODIFY `game_id` smallint unsigned NOT NULL,
	MODIFY `max_ports` tinyint NOT NULL,
	MODIFY `start_sector_id` smallint unsigned NOT NULL,
	MODIFY `end_sector_id` smallint unsigned NOT NULL,
	MODIFY `routes_for_port` smallint NOT NULL,
	MODIFY `max_distance` smallint NOT NULL;

ALTER TABLE `sector`
	MODIFY `sector_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `link_up` smallint unsigned NULL DEFAULT NULL,
	MODIFY `link_down` smallint unsigned NULL DEFAULT NULL,
	MODIFY `link_left` smallint unsigned NULL DEFAULT NULL,
	MODIFY `link_right` smallint unsigned NULL DEFAULT NULL,
	MODIFY `warp` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `battles` mediumint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `sector_has_forces`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `sector_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `combat_drones` tinyint unsigned NOT NULL DEFAULT '0',
	MODIFY `scout_drones` tinyint unsigned NOT NULL DEFAULT '0',
	MODIFY `mines` tinyint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `sector_message`
	MODIFY `game_id` smallint NOT NULL DEFAULT '0';

ALTER TABLE `ship_has_cargo`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `good_id` tinyint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `ship_has_hardware`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `hardware_type_id` tinyint unsigned NOT NULL DEFAULT '0';

-- Invalid negative illusion ratings were stored in the unsigned int columns
-- as 4,294,967,295. Clamp all out-of-range ratings before narrowing them.
UPDATE `ship_has_illusion`
	SET `attack` = LEAST(`attack`, 65535),
		`defense` = LEAST(`defense`, 65535)
	WHERE `attack` > 65535 OR `defense` > 65535;
ALTER TABLE `ship_has_illusion`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `ship_type_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `attack` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `defense` smallint unsigned NOT NULL DEFAULT '0';

ALTER TABLE `ship_has_name`
	MODIFY `game_id` smallint NOT NULL DEFAULT '0';
ALTER TABLE `ship_has_weapon`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `order_id` tinyint unsigned NOT NULL DEFAULT '0',
	MODIFY `weapon_type_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `ship_is_cloaked`
	MODIFY `game_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `ship_type`
	MODIFY `ship_type_id` smallint unsigned NOT NULL AUTO_INCREMENT,
	MODIFY `speed` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `race_id` tinyint unsigned NOT NULL DEFAULT '1',
	MODIFY `ship_class_id` tinyint unsigned NOT NULL DEFAULT '1',
	MODIFY `hardpoint` tinyint unsigned NOT NULL DEFAULT '0',
	MODIFY `lvl_needed` tinyint unsigned NOT NULL DEFAULT '0',
	MODIFY `buyer_restriction` tinyint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `ship_type_support_hardware`
	MODIFY `ship_type_id` smallint unsigned NOT NULL DEFAULT '0',
	MODIFY `hardware_type_id` tinyint unsigned NOT NULL DEFAULT '0';

ALTER TABLE `voting_results`
	MODIFY `account_id` smallint unsigned NOT NULL;
ALTER TABLE `weapon_type`
	MODIFY `weapon_type_id` smallint unsigned NOT NULL AUTO_INCREMENT,
	MODIFY `race_id` tinyint unsigned NOT NULL,
	MODIFY `shield_damage` smallint unsigned NOT NULL,
	MODIFY `armour_damage` smallint unsigned NOT NULL,
	MODIFY `accuracy` tinyint unsigned NOT NULL,
	MODIFY `power_level` tinyint unsigned NOT NULL,
	MODIFY `buyer_restriction` tinyint unsigned NOT NULL;
ALTER TABLE `weighted_random`
	MODIFY `game_id` smallint unsigned NOT NULL;
