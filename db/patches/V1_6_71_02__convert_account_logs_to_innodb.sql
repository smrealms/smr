-- MyISAM assigns log_id separately for each account because account_id is
-- the leftmost primary-key column. Assign global IDs before converting it
-- into an InnoDB primary key.
ALTER TABLE account_has_logs
	MODIFY log_id INT UNSIGNED NOT NULL;

ALTER TABLE account_has_logs
	ADD new_log_id INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
	ADD UNIQUE KEY (new_log_id);

ALTER TABLE account_has_logs
	DROP PRIMARY KEY,
	DROP log_id,
	RENAME COLUMN new_log_id TO log_id,
	ADD PRIMARY KEY (log_id);

ALTER TABLE account_has_logs
	DROP KEY new_log_id,
	ADD KEY (account_id),
	ENGINE = InnoDB;
