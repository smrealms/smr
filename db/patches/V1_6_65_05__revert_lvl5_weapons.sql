-- Revert accuracy of HHG/Nuke back to 35%
UPDATE weapon_type SET accuracy = 35 WHERE weapon_name = 'Nuke';
UPDATE weapon_type SET accuracy = 35 WHERE weapon_name = 'Holy Hand Grenade';
