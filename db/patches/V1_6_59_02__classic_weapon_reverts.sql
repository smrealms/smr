/* changes to weapons as per JJ */

-- HHG to 300/0
UPDATE weapon_type SET shield_damage=300 WHERE weapon_type_id = 3;

-- Nuke to 0/300
UPDATE weapon_type SET armour_damage=300 WHERE weapon_type_id = 2;

-- PPL to 150/150
UPDATE weapon_type SET shield_damage=150, armour_damage=150 WHERE weapon_type_id = 55;

-- Drone cost to 5k
UPDATE hardware_type SET cost=5000 WHERE hardware_type_id = 4;