CREATE TABLE smr_new.hof_visibility (
 	type VARCHAR(255) NOT NULL
,	visibility ENUM('PUBLIC', 'ALLIANCE', 'PRIVATE') NOT NULL
,	PRIMARY KEY (type)
) ENGINE = MYISAM