-- Devastator -1 hp (8 -> 7)
UPDATE ship_type SET hardpoint = 7 WHERE ship_type_id = 38;

-- Dark Mirage +25 shields (825 -> 850)
UPDATE ship_type_support_hardware SET max_amount = 850 WHERE ship_type_id = 67 AND hardware_type_id = 1;
-- Dark Mirage +25 armour (825 -> 850)
UPDATE ship_type_support_hardware SET max_amount = 850 WHERE ship_type_id = 67 AND hardware_type_id = 2;

-- Destroyer +50 armour (750 -> 800)
UPDATE ship_type_support_hardware SET max_amount = 800 WHERE ship_type_id = 43 AND hardware_type_id = 2;

-- Assault Craft +5 scouts (0 -> 5)
UPDATE ship_type_support_hardware SET max_amount = 5 WHERE ship_type_id = 61 AND hardware_type_id = 5;

-- Trade-Master +Cloak
UPDATE ship_type_support_hardware SET max_amount = 1 WHERE ship_type_id = 33 AND hardware_type_id = 8;
-- Trade-Master +Illusion
UPDATE ship_type_support_hardware SET max_amount = 1 WHERE ship_type_id = 33 AND hardware_type_id = 9;
-- Trade-Master +Jump
UPDATE ship_type_support_hardware SET max_amount = 1 WHERE ship_type_id = 33 AND hardware_type_id = 10;
-- Trade-Master -5 scouts (25 -> 20)
UPDATE ship_type_support_hardware SET max_amount = 20 WHERE ship_type_id = 33 AND hardware_type_id = 5;
-- Trade-Master -25 mines (100 -> 75)
UPDATE ship_type_support_hardware SET max_amount = 75 WHERE ship_type_id = 33 AND hardware_type_id = 6;

-- Death Cruiser +5 scouts (20 -> 25)
UPDATE ship_type_support_hardware SET max_amount = 25 WHERE ship_type_id = 25 AND hardware_type_id = 5;
