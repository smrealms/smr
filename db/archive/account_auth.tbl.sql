CREATE TABLE  `smr_new`.`account_auth` (
`account_id` SMALLINT UNSIGNED NOT NULL ,
`login_type` VARCHAR( 100 ) NOT NULL ,
`auth_key` VARCHAR( 100 ) NOT NULL ,
PRIMARY KEY (  `account_id` ,  `login_type` ,  `auth_key` )
) ENGINE = MYISAM ;