-- Devastator -50 shields (300 -> 250)
UPDATE ship_type_support_hardware SET max_amount = 250 WHERE ship_type_id = 38 AND hardware_type_id = 1;
-- Devastator +50 armour (1800 -> 1850)
UPDATE ship_type_support_hardware SET max_amount = 1850 WHERE ship_type_id = 38 AND hardware_type_id = 2;
