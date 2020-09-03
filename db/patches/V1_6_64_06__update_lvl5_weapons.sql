-- Decrease damage of HHG/Nuke by 10%
UPDATE weapon_type SET armour_damage = 270 WHERE weapon_name = 'Nuke';
UPDATE weapon_type SET shield_damage = 270 WHERE weapon_name = 'Holy Hand Grenade';
