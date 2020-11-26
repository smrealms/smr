-- Make HPT a bit stronger than the PT
UPDATE weapon_type SET armour_damage = 170, accuracy = 43 WHERE weapon_name = 'Human Photon Torpedo';
UPDATE weapon_type SET accuracy = 41 WHERE weapon_name = 'Photon Torpedo';
