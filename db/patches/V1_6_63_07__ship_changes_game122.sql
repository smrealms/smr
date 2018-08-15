-- Thief +10 holds (150 -> 160)
UPDATE ship_type_support_hardware SET max_amount = 160 WHERE ship_type_id = 23 AND hardware_type_id = 3;

-- Assasin +1 hp (4 -> 5)
UPDATE ship_type SET hardpoint = 5 WHERE ship_type_id = 24;

-- Death Cruiser +1 hp (5 -> 6)
UPDATE ship_type SET hardpoint = 6 WHERE ship_type_id = 25;
-- Death Cruiser -25 mines (125 -> 100)
UPDATE ship_type_support_hardware SET max_amount = 100 WHERE ship_type_id = 25 AND hardware_type_id = 6;

-- Advanced Carrier -50 shields (750 -> 700)
UPDATE ship_type_support_hardware SET max_amount = 700 WHERE ship_type_id = 48 AND hardware_type_id = 1;
-- Advanced Carrier +50 armour (100 -> 150)
UPDATE ship_type_support_hardware SET max_amount = 150 WHERE ship_type_id = 48 AND hardware_type_id = 2;
-- Advanced Carrier -25 CDs (275 -> 250)
UPDATE ship_type_support_hardware SET max_amount = 250 WHERE ship_type_id = 48 AND hardware_type_id = 4;
