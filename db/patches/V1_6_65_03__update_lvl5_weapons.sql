-- Decrease accuracy of HHG/Nuke by 5%
UPDATE weapon_type SET accuracy = 30 WHERE weapon_name = 'Nuke';
UPDATE weapon_type SET accuracy = 30 WHERE weapon_name = 'Holy Hand Grenade';
