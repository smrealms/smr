-- Remove all existing rows of `player_has_mission`. Assumes we are not mid-game.
TRUNCATE TABLE `player_has_mission`;

-- Rename step_fails -> expires (better match for its intended behavior).
ALTER TABLE `player_has_mission` RENAME COLUMN `step_fails` TO `expires`;

-- Add `complete` boolean column to track if the player has completed a mission.
ALTER TABLE `player_has_mission`
	ADD COLUMN `complete` enum('TRUE','FALSE') NOT NULL;

-- Add `mission` text column to hold the polymorphic Mission instance.
ALTER TABLE `player_has_mission`
	ADD COLUMN `mission` TEXT NOT NULL;

-- Drop unused columns
ALTER TABLE `player_has_mission`
	DROP COLUMN `progress`,
	DROP COLUMN `starting_sector`,
	DROP COLUMN `mission_sector`;
