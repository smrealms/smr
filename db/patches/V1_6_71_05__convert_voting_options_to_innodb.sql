-- MyISAM assigns option_id separately for each vote. Give existing options
-- global IDs and remap their results before making option_id the primary key.
-- Align vote_id and option_id with their unsigned voting and voting_results
-- counterparts while rebuilding the table.
ALTER TABLE voting_options
	MODIFY vote_id INT UNSIGNED NOT NULL,
	MODIFY option_id INT UNSIGNED NOT NULL;

ALTER TABLE voting_options
	ADD new_option_id INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
	ADD UNIQUE KEY (new_option_id);

UPDATE voting_results
JOIN voting_options USING (vote_id, option_id)
SET voting_results.option_id = voting_options.new_option_id;

ALTER TABLE voting_options
	DROP PRIMARY KEY,
	DROP option_id,
	RENAME COLUMN new_option_id TO option_id,
	ADD PRIMARY KEY (option_id);

ALTER TABLE voting_options
	DROP KEY new_option_id,
	ADD KEY (vote_id),
	ENGINE = InnoDB;
