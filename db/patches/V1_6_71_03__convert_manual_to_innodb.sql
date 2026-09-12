-- topic_id is globally generated. Make it the sole key so that uniqueness is
-- enforced directly instead of being implied by the old composite key.
ALTER TABLE manual
	DROP PRIMARY KEY,
	ADD PRIMARY KEY (topic_id),
	ENGINE = InnoDB;
