-- Add table to track locations that have enhanced weapon events
CREATE TABLE location_sells_special (
  game_id int unsigned NOT NULL,
  sector_id int unsigned NOT NULL,
  location_type_id int unsigned NOT NULL,
  weapon_type_id int unsigned NOT NULL,
  expires int unsigned NOT NULL,
  bonus_accuracy enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  bonus_damage enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
  PRIMARY KEY (game_id, sector_id, location_type_id, weapon_type_id)
);
