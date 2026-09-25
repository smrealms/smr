-- log_id is already globally generated because it is the leftmost MyISAM
-- primary-key column. Use it as the sole InnoDB primary key after widening it.
-- The remaining columns in the old composite key added no lookup value: an
-- index led by log_id cannot serve queries that filter by those columns.
ALTER TABLE combat_logs
	MODIFY log_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	DROP PRIMARY KEY,
	ADD PRIMARY KEY (log_id),
	ENGINE = InnoDB;
