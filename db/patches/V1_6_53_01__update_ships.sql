-- Bounty hunter to 2,368,634 credits
UPDATE ship_type SET cost = 2368634 WHERE ship_type_id = 59;

-- Resistance to 2,056,265 credits
UPDATE ship_type SET cost = 2056265 WHERE ship_type_id = 64;

-- Carapace to 8,045,094 credits
UPDATE ship_type SET cost = 8045094 WHERE ship_type_id = 60;

-- Rogue to 8,045,094 credits
UPDATE ship_type SET cost = 8045094, hardpoint = 6 WHERE ship_type_id = 65;

-- Expediter to 4 hardpoints, 15 scouts, 15 mines, and 155 holds
UPDATE ship_type SET hardpoint = 4 WHERE ship_type_id = 57;
UPDATE ship_type_support_hardware SET max_amount = 155 WHERE ship_type_id = 57 AND hardware_type_id = 3;
UPDATE ship_type_support_hardware SET max_amount = 15 WHERE ship_type_id = 57 AND hardware_type_id = 5;
UPDATE ship_type_support_hardware SET max_amount = 15 WHERE ship_type_id = 57 AND hardware_type_id = 6;

-- Planetary Freighter to 450 holds
UPDATE ship_type_support_hardware SET max_amount = 450 WHERE ship_type_id = 11 AND hardware_type_id = 3;

-- Planetary Super Freighter to 500 holds
UPDATE ship_type_support_hardware SET max_amount = 500 WHERE ship_type_id = 12 AND hardware_type_id = 3;

-- Blockade Runner to 4 hardpoints
UPDATE ship_type SET hardpoint = 4 WHERE ship_type_id = 66;

-- Assault Craft to 7 hardpoints, 850 shields, 850 armour, 10 scouts, 30 CDs, and 25 mines.
UPDATE ship_type SET hardpoint = 7 WHERE ship_type_id = 61;
UPDATE ship_type_support_hardware SET max_amount = 815 WHERE ship_type_id = 61 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 815 WHERE ship_type_id = 61 AND hardware_type_id = 2;
UPDATE ship_type_support_hardware SET max_amount = 30 WHERE ship_type_id = 61 AND hardware_type_id = 4;
UPDATE ship_type_support_hardware SET max_amount = 10 WHERE ship_type_id = 61 AND hardware_type_id = 5;
UPDATE ship_type_support_hardware SET max_amount = 25 WHERE ship_type_id = 61 AND hardware_type_id = 6;

-- Dark Mirage to 7 hardpoints, 750 shields, 750 armour, and 10 scouts.
UPDATE ship_type SET hardpoint = 7 WHERE ship_type_id = 67;
UPDATE ship_type_support_hardware SET max_amount = 750 WHERE ship_type_id = 67 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 750 WHERE ship_type_id = 67 AND hardware_type_id = 2;
UPDATE ship_type_support_hardware SET max_amount = 10 WHERE ship_type_id = 67 AND hardware_type_id = 5;

-- Devastator to 6 hardpoints and 2000 armour.
UPDATE ship_type SET hardpoint = 5 WHERE ship_type_id = 38;
UPDATE ship_type_support_hardware SET max_amount = 2000 WHERE ship_type_id = 38 AND hardware_type_id = 2;

-- Destroyer to 5 hardpoints, 875 shields, 775 armour, and 75 CDs.
UPDATE ship_type SET hardpoint = 5 WHERE ship_type_id = 43;
UPDATE ship_type_support_hardware SET max_amount = 900 WHERE ship_type_id = 43 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 825 WHERE ship_type_id = 43 AND hardware_type_id = 2;
UPDATE ship_type_support_hardware SET max_amount = 75 WHERE ship_type_id = 43 AND hardware_type_id = 4;

-- Eater of Souls to 5 hardpoints.
UPDATE ship_type SET hardpoint = 5 WHERE ship_type_id = 55;

-- Fury to 6 hardpoints and 1100 shields.
UPDATE ship_type SET hardpoint = 6 WHERE ship_type_id = 75;
UPDATE ship_type_support_hardware SET max_amount = 1100 WHERE ship_type_id = 75 AND hardware_type_id = 1;

-- Mothership to 1100 shields.
UPDATE ship_type_support_hardware SET max_amount = 1075 WHERE ship_type_id = 49 AND hardware_type_id = 1;
