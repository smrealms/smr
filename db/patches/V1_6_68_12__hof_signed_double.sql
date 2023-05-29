-- Remove unsigned attribute from `player_hof.amount`
ALTER TABLE `player_hof` MODIFY `amount` double NOT NULL;
