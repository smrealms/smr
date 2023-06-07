-- Make changes needed to enable strict SQL mode
ALTER TABLE `chess_game` MODIFY `winner_id` int unsigned NOT NULL DEFAULT 0;
