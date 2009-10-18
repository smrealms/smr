CREATE TABLE feature_request_comments (
feature_request_id INT UNSIGNED NOT NULL ,
comment_id INT UNSIGNED NOT NULL AUTO_INCREMENT ,
poster_id INT UNSIGNED NOT NULL ,
posting_time INT UNSIGNED NOT NULL ,
anonymous enum('TRUE','FALSE') NOT NULL DEFAULT 'TRUE',
text TEXT NOT NULL ,
PRIMARY KEY ( feature_request_id , comment_id )
);

INSERT INTO feature_request_comments
SELECT feature_request_id, null, submitter_id, UNIX_TIMESTAMP(), 'TRUE', feature
FROM feature_request;

ALTER TABLE feature_request DROP feature;
ALTER TABLE feature_request DROP submitter_id;