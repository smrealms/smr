-- Proto Carrier to 1,453,810 credits
UPDATE ship_type SET cost = 1453810 WHERE ship_type_id = 47;

-- Battle Cruiser to 8,628,134 credits, 6 hardpoints, 500 shields, and 425 armour
UPDATE ship_type SET cost = 8628134, hardpoint = 6 WHERE ship_type_id = 17;
UPDATE ship_type_support_hardware SET max_amount = 500 WHERE ship_type_id = 17 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 425 WHERE ship_type_id = 17 AND hardware_type_id = 2;

-- Carapace to 9,045,094 credits, 6 hardpoints, 475 shields, and 585 armour
UPDATE ship_type SET cost = 9045094, hardpoint = 6 WHERE ship_type_id = 60;
UPDATE ship_type_support_hardware SET max_amount = 475 WHERE ship_type_id = 60 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 585 WHERE ship_type_id = 60 AND hardware_type_id = 2;

-- Juggernaut to 7,404,130 credits, 7 hardpoints, 100 shields, and 1250 armour
UPDATE ship_type SET cost = 7404130, hardpoint = 7 WHERE ship_type_id = 37;
UPDATE ship_type_support_hardware SET max_amount = 100 WHERE ship_type_id = 37 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 1250 WHERE ship_type_id = 37 AND hardware_type_id = 2;

-- Ravager to 8,307,691 credits, 6 hardpoints, 625 shields, and 675 armour
UPDATE ship_type SET cost = 8307691, hardpoint = 6 WHERE ship_type_id = 54;
UPDATE ship_type_support_hardware SET max_amount = 625 WHERE ship_type_id = 54 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 675 WHERE ship_type_id = 54 AND hardware_type_id = 2;

-- Vindicator to 8,400,000 credits, 7 hardpoints, 400 shields, and 375 armour
UPDATE ship_type SET cost = 8400000, hardpoint = 7 WHERE ship_type_id = 74;
UPDATE ship_type_support_hardware SET max_amount = 400 WHERE ship_type_id = 74 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 375 WHERE ship_type_id = 74 AND hardware_type_id = 2;

-- Advanced Carrier to 7,110,264 credits, 615 shields, 40 armour, and 290 CDs
UPDATE ship_type SET cost = 7110264 WHERE ship_type_id = 48;
UPDATE ship_type_support_hardware SET max_amount = 615 WHERE ship_type_id = 48 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 40 WHERE ship_type_id = 48 AND hardware_type_id = 2;
UPDATE ship_type_support_hardware SET max_amount = 290 WHERE ship_type_id = 48 AND hardware_type_id = 4;

-- Medium Carrier to 6,030,523 credits, 425 shields, 40 armour, and 220 CDs
UPDATE ship_type SET cost = 6030523 WHERE ship_type_id = 59;
UPDATE ship_type_support_hardware SET max_amount = 425 WHERE ship_type_id = 27 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 40 WHERE ship_type_id = 27 AND hardware_type_id = 2;
UPDATE ship_type_support_hardware SET max_amount = 220 WHERE ship_type_id = 27 AND hardware_type_id = 4;

-- Death Cruiser to 6 hardpoints and 600 shields
UPDATE ship_type SET hardpoint = 6 WHERE ship_type_id = 25;
UPDATE ship_type_support_hardware SET max_amount = 600 WHERE ship_type_id = 25 AND hardware_type_id = 1;

-- Rogue to 8,574,860 credits, 6 hardpoints, 325 shields, and 400 armour
UPDATE ship_type SET cost = 8574860, hardpoint = 6 WHERE ship_type_id = 65;
UPDATE ship_type_support_hardware SET max_amount = 325 WHERE ship_type_id = 65 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 400 WHERE ship_type_id = 65 AND hardware_type_id = 2;

-- Border Cruiser to 8,887,432 credits, 6 hardpoints, 425 shields, and 425 armour
UPDATE ship_type SET cost = 8887432, hardpoint = 6 WHERE ship_type_id = 42;
UPDATE ship_type_support_hardware SET max_amount = 425 WHERE ship_type_id = 42 AND hardware_type_id = 1;
UPDATE ship_type_support_hardware SET max_amount = 425 WHERE ship_type_id = 42 AND hardware_type_id = 2;

-- Federal Ultimatum to 8 hardpoints and 450 armour
UPDATE ship_type SET hardpoint = 8 WHERE ship_type_id = 22;
UPDATE ship_type_support_hardware SET max_amount = 450 WHERE ship_type_id = 22 AND hardware_type_id = 2;

-- Mothership to 15,932,759 credits and 7 speed
UPDATE ship_type SET cost = 15932759, speed = 7 WHERE ship_type_id = 49;

-- Devastator to 7 hardpoints and 7 speed
UPDATE ship_type SET hardpoint = 7, speed = 7 WHERE ship_type_id = 38;
