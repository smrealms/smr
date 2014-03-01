-- Retribution to 8 hardpoints, 350 shields, 230 armour, and 0 CDs
UPDATE ship_type SET hardpoint = 8 WHERE ship_type_id = 73;
UPDATE ship_type_support_hardware SET max_amount = 350 WHERE ship_type_id = 73 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 230 WHERE ship_type_id = 73 AND hardware_type_id = 2;
UPDATE ship_type_support_hardware SET max_amount = 0 WHERE ship_type_id = 73 AND hardware_type_id = 3;

-- Negotiator to 3 hardpoints
UPDATE ship_type SET hardpoint = 3 WHERE ship_type_id = 63;

-- Vindicator to 8 hardpoints, 475 shields, and 0 CDs
UPDATE ship_type SET hardpoint = 8 WHERE ship_type_id = 74;
UPDATE ship_type_support_hardware SET max_amount = 475 WHERE ship_type_id = 74 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 0 WHERE ship_type_id = 74 AND hardware_type_id = 3;

-- Planetary Freighter to 450 shields and 900 armour
UPDATE ship_type_support_hardware SET max_amount = 450 WHERE ship_type_id = 11 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 900 WHERE ship_type_id = 11 AND hardware_type_id = 2;

-- Planetary Super Freighter to 650 shields and 1300 armour
UPDATE ship_type_support_hardware SET max_amount = 650 WHERE ship_type_id = 12 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 1300 WHERE ship_type_id = 12 AND hardware_type_id = 2;

-- Destroyer to 875 armour
UPDATE ship_type_support_hardware SET max_amount = 875 WHERE ship_type_id = 43 AND hardware_type_id = 2;

-- Devastator to 2100 armour.
UPDATE ship_type_support_hardware SET max_amount = 2100 WHERE ship_type_id = 38 AND hardware_type_id = 2;

-- Eater of Souls to 8tph
UPDATE ship_type SET speed = 8 WHERE ship_type_id = 55;

-- Rebellious Child to 3 hardpoints, 325 shields, and 50 armour
UPDATE ship_type SET hardpoint = 3 WHERE ship_type_id = 45;
UPDATE ship_type_support_hardware SET max_amount = 325 WHERE ship_type_id = 45 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 50 WHERE ship_type_id = 45 AND hardware_type_id = 2;

-- Star Ranger to 5 hardpoints, 175 shields, and 150 armour
UPDATE ship_type SET hardpoint = 5 WHERE ship_type_id = 58;
UPDATE ship_type_support_hardware SET max_amount = 175 WHERE ship_type_id = 58 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 150 WHERE ship_type_id = 58 AND hardware_type_id = 2;

-- Unarmed Scout to 300 shields and 175 armour
UPDATE ship_type_support_hardware SET max_amount = 300 WHERE ship_type_id = 13 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 175 WHERE ship_type_id = 13 AND hardware_type_id = 2;

-- Watchful Eye to 3 hardpoints, 150 shields, and 200 armour
UPDATE ship_type SET hardpoint = 5 WHERE ship_type_id = 52;
UPDATE ship_type_support_hardware SET max_amount = 150 WHERE ship_type_id = 52 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 200 WHERE ship_type_id = 52 AND hardware_type_id = 2;
