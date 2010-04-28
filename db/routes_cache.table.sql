CREATE TABLE `smr_new`.`route_cache`
(
	`game_id` INT UNSIGNED NOT NULL
,	`max_ports` INT NOT NULL
,	`goods_allowed` VARCHAR(100) NOT NULL
,	`races_allowed` VARCHAR(100) NOT NULL
,	`start_sector_id` INT UNSIGNED NOT NULL
,	`end_sector_id` INT UNSIGNED NOT NULL
,	`routes_for_port` INT NOT NULL
,	`max_distance` INT NOT NULL
,	`routes` BLOB NOT NULL
,	PRIMARY KEY (`game_id`, `max_ports`, `goods_allowed`, `races_allowed`, `start_sector_id`, `end_sector_id`, `routes_for_port`)
) ENGINE = MyISAM;