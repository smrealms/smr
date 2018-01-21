-- Add an "Accepted" status for feature requests
ALTER TABLE feature_request MODIFY COLUMN status enum('Opened','Implemented','Rejected','Deleted','Accepted') NOT NULL;
