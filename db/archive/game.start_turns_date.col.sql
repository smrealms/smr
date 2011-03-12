ALTER TABLE  `game` ADD  `start_turns_date` INT UNSIGNED NOT NULL AFTER  `start_date`;
UPDATE game SET start_turns_date = start_date;