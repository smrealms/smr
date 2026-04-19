-- Add alliance role permission to manage hired NPCs
ALTER TABLE `alliance_has_roles`
	ADD COLUMN `manage_npcs` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE';
