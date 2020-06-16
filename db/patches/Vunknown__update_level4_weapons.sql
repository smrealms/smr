-- Increase damage of Level 4 weapons by 15% to bring power efficiency in step with other power levels
UPDATE weapon_type SET armour_damage = 230 WHERE weapon_name = 'Big Momma Torpedo Launcher';
UPDATE weapon_type SET armour_damage = 115 WHERE weapon_name = 'Little Junior Torpedo';
UPDATE weapon_type SET shield_damage = 290 WHERE weapon_name = 'WQ Human Shield Vaporizer';
UPDATE weapon_type SET shield_damage = 90, armour_damage = 90 WHERE weapon_name = 'Huge Pulse Laser';
UPDATE weapon_type SET armour_damage = 290 WHERE weapon_name = 'Creonti "Big Daddy"';
UPDATE weapon_type SET shield_damage = 200 WHERE weapon_name = 'Thevian Shield Disperser';
UPDATE weapon_type SET armour_damage = 200 WHERE weapon_name = 'Nijarin Claymore Missile';
UPDATE weapon_type SET shield_damage = 55, armour_damage = 55 WHERE weapon_name = 'Alskant Focused Laser';
UPDATE weapon_type SET shield_damage = 115 WHERE weapon_name = 'Human Harmonic Disruptor';