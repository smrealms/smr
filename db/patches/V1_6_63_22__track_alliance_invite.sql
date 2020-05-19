-- Add message_id column to alliance_invites_player table
ALTER TABLE `alliance_invites_player` ADD COLUMN `message_id` mediumint(8) unsigned NOT NULL;
