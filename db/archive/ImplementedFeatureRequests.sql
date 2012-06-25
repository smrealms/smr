ALTER TABLE `feature_request` ADD `implemented` ENUM( 'TRUE', 'FALSE' ) NOT NULL DEFAULT 'FALSE',
	ADD `fav` INT NOT NULL ,
	ADD `yes` INT NOT NULL ,
	ADD `no` INT NOT NULL