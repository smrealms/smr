-- Add two new neutral anti-shield weapons.  One P3 and one P4.
INSERT INTO `weapon_type` (`weapon_type_id`, `weapon_name`, `race_id`, `cost`, `shield_damage`, `armour_damage`, `accuracy`, `power_level`, `buyer_restriction`) VALUES
(56, 'Resonant Shield Disruptor', 1, 99000, 110, 0, 65, 3, 0),
(57, 'Harmonic Shield Disruptor', 1, 199000, 125, 0, 62, 4, 0);
