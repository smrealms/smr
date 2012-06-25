ALTER TABLE smr_new.npc_logins
ADD player_name VARCHAR(32) NOT NULL AFTER login,
ADD alliance_id INT UNSIGNED NOT NULL AFTER player_name;

UPDATE smr_new.npc_logins SET player_name = login;

ALTER TABLE smr_new.npc_logins
ADD UNIQUE (player_name)