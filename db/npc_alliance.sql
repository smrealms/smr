ALTER TABLE  `npc_logins` CHANGE  `alliance_id`  `alliance_name` VARCHAR( 32 ) NOT NULL DEFAULT  'No Alliance';
ALTER TABLE  `npc_logins` ADD  `active` ENUM(  'TRUE',  'FALSE' ) NOT NULL DEFAULT  'TRUE' AFTER  `alliance_name`;
UPDATE npc_logins SET alliance_name =  'Fellowship of the ring';
