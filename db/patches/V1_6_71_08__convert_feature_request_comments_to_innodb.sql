-- MyISAM assigned comment_id separately for each feature request. Give every
-- comment a global ID before using it as the InnoDB primary key.
ALTER TABLE feature_request_comments
	MODIFY comment_id INT UNSIGNED NOT NULL;

ALTER TABLE feature_request_comments
	ADD new_comment_id INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
	ADD UNIQUE KEY (new_comment_id);

ALTER TABLE feature_request_comments
	DROP PRIMARY KEY,
	DROP comment_id,
	RENAME COLUMN new_comment_id TO comment_id,
	ADD PRIMARY KEY (comment_id);

-- Keep the per-feature-request paths used to find the original comment and
-- to check comment activity without retaining the former composite key.
ALTER TABLE feature_request_comments
	DROP KEY new_comment_id,
	ADD KEY feature_request_id_comment_id (feature_request_id, comment_id),
	ADD KEY feature_request_id_posting_time (feature_request_id, posting_time),
	ENGINE = InnoDB;
