-- Add fields for bonus accuracy/damage for weapons
ALTER TABLE ship_has_weapon ADD COLUMN bonus_accuracy enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE';
ALTER TABLE ship_has_weapon ADD COLUMN bonus_damage enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE';
ALTER TABLE planet_has_weapon ADD COLUMN bonus_accuracy enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE';
ALTER TABLE planet_has_weapon ADD COLUMN bonus_damage enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE';
