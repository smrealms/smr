CREATE TABLE  `smr_new`.`npc_logins`
(
	`login` VARCHAR( 32 ) NOT NULL ,
	`working` ENUM(  'TRUE',  'FALSE' ) NOT NULL DEFAULT  'FALSE',
	PRIMARY KEY (  `login` )
) ENGINE = MYISAM ;