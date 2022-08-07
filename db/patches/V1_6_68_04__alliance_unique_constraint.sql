-- Add constraints to ensure no two alliances have the same name
ALTER TABLE `alliance` ADD UNIQUE (`alliance_name`, `game_id`);
