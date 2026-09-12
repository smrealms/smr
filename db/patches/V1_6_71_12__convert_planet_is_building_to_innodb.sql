-- building_slot_id remains a per-planet slot number and is allocated by PHP.
ALTER TABLE planet_is_building
	MODIFY building_slot_id INT UNSIGNED NOT NULL,
	ENGINE = InnoDB;
