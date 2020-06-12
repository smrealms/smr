-- Roughly double the cost of shields/armour
UPDATE hardware_type SET cost = 250 WHERE hardware_name = 'Shields';
UPDATE hardware_type SET cost = 150 WHERE hardware_name = 'Armor';

-- Use British English spelling of armour for consistency
UPDATE hardware_type SET hardware_name = 'Armour' where hardware_name = 'Armor';
