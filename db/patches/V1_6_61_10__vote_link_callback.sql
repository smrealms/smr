-- Add turns_claimed column to vote_links table
ALTER TABLE `vote_links` ADD COLUMN `turns_claimed` enum('TRUE','FALSE') NOT NULL;
