-- Negotiator cost increase x4 (155,234 -> 620,936)
UPDATE ship_type SET cost = 620936 WHERE ship_type_id = 63;
-- Negotiator +50 shields (200 -> 250)
UPDATE ship_type_support_hardware SET max_amount = 250 where ship_type_id = 63 AND hardware_type_id = 1;
-- Negotiator +50 armor (150 -> 200)
UPDATE ship_type_support_hardware SET max_amount = 200 where ship_type_id = 63 AND hardware_type_id = 2;
-- Negotiator +90 holds (40 -> 130)
UPDATE ship_type_support_hardware SET max_amount = 130 where ship_type_id = 63 AND hardware_type_id = 3;
-- Negotiator add cloak
UPDATE ship_type_support_hardware SET max_amount = 1 where ship_type_id = 63 AND hardware_type_id = 8;

-- Resistance +1 hardpoint (2 -> 3)
UPDATE ship_type SET hardpoint = 3 where ship_type_id = 64;

-- Rogue cost increase +50% (2,074,860 -> 3,112,290)
UPDATE ship_type SET cost = 465702 WHERE ship_type_id = 65;
-- Rogue +2 hardpoints (2 -> 4)
UPDATE ship_type SET hardpoint = 4 where ship_type_id = 65;
-- Rogue -150 armor (550 -> 400)
UPDATE ship_type_support_hardware SET max_amount = 400 where ship_type_id = 65 AND hardware_type_id = 2;

-- Blockade Runner -50% cost (5,131,071 -> 2,565,536)
UPDATE ship_type SET cost = 2565536 WHERE ship_type_id = 66;
-- Blockade Runner -1 hardpoint (3 -> 2_)
UPDATE ship_type SET hardpoint = 2 where ship_type_id = 66;
-- Blockade Runner -100 shields (550 -> 450)
UPDATE ship_type_support_hardware SET max_amount = 450 where ship_type_id = 66 AND hardware_type_id = 1;
-- Blockade Runner -100 armor (500 -> 400)
UPDATE ship_type_support_hardware SET max_amount = 400 where ship_type_id = 66 AND hardware_type_id = 2;
-- Blockade Runner +15 holds (175 -> 190)
UPDATE ship_type_support_hardware SET max_amount = 190 where ship_type_id = 66 AND hardware_type_id = 3;