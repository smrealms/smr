-- Making names of date-related game fields more transparent
ALTER TABLE game CHANGE start_date join_time int unsigned NOT NULL;
ALTER TABLE game CHANGE start_turns_date start_time int unsigned NOT NULL;
ALTER TABLE game CHANGE end_date end_time int unsigned NOT NULL;
