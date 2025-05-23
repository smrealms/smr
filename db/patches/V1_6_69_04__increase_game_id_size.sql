-- Increase game_id from tinyint to smallint
ALTER TABLE `active_session` MODIFY COLUMN `game_id` smallint unsigned NOT NULL DEFAULT '0';
ALTER TABLE `alliance_has_op` MODIFY COLUMN `game_id` smallint unsigned NOT NULL;
ALTER TABLE `alliance_has_op_response` MODIFY COLUMN `game_id` smallint unsigned NOT NULL;
ALTER TABLE `alliance_has_roles` MODIFY COLUMN `game_id` smallint unsigned NOT NULL; -- removed DEFAULT '0'
ALTER TABLE `alliance_has_seedlist` MODIFY COLUMN `game_id` smallint unsigned NOT NULL;
ALTER TABLE `alliance_thread` MODIFY COLUMN `game_id` smallint unsigned NOT NULL; -- removed DEFAULT '0'
ALTER TABLE `alliance_treaties` MODIFY COLUMN `game_id` smallint unsigned NOT NULL; -- add unsigned, remove DEFAULT '0'
ALTER TABLE `combat_logs` MODIFY COLUMN `game_id` smallint unsigned NOT NULL; -- removed DEFAULT '0'
ALTER TABLE `game_create_status` MODIFY COLUMN `game_id` smallint unsigned NOT NULL;
ALTER TABLE `locks_queue` MODIFY COLUMN `game_id` smallint unsigned NOT NULL; -- removed DEFAULT '0'
ALTER TABLE `message` MODIFY COLUMN `game_id` smallint unsigned NOT NULL; -- removed DEFAULT '0'
ALTER TABLE `player` MODIFY COLUMN `game_id` smallint unsigned NOT NULL; -- removed DEFAULT '0'
ALTER TABLE `player_attacks_port` MODIFY COLUMN `game_id` smallint unsigned NOT NULL; -- removed DEFAULT '0'
ALTER TABLE `player_has_alliance_role` MODIFY COLUMN `game_id` smallint unsigned NOT NULL; -- removed DEFAULT '0'
ALTER TABLE `player_has_notes` MODIFY COLUMN `game_id` smallint unsigned NOT NULL; -- removed DEFAULT '0'
ALTER TABLE `player_joined_alliance` MODIFY COLUMN `game_id` smallint unsigned NOT NULL;
ALTER TABLE `player_read_thread` MODIFY COLUMN `game_id` smallint unsigned NOT NULL; -- removed DEFAULT '0'
ALTER TABLE `player_saved_combat_logs` MODIFY COLUMN `game_id` smallint unsigned NOT NULL;
ALTER TABLE `player_visited_port` MODIFY COLUMN `game_id` smallint unsigned NOT NULL; -- removed DEFAULT '0'
