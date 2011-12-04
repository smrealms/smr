ALTER TABLE smr_new.feature_request ADD status ENUM( 'Opened', 'Implemented', 'Rejected', 'Deleted' ) NOT NULL;
UPDATE smr_new.feature_request SET status = 'Opened';
UPDATE smr_new.feature_request SET status = 'Implemented' WHERE implemented = 'TRUE';
ALTER TABLE smr_new.feature_request DROP implemented;