-- Federal Discovery to 800 shields
UPDATE ship_type_support_hardware SET max_amount = 800 WHERE ship_type_id = 20 AND hardware_type_id = 1;

-- Federal Warrant to 5,026,598 credits and 0 mines.
UPDATE ship_type SET cost = 5026598 WHERE ship_type_id = 21;
-- Federal Warrant to 0 mines
UPDATE ship_type_support_hardware SET max_amount = 0 WHERE ship_type_id = 21 AND hardware_type_id = 6;

-- Federal Ultimatum to 21,275,738 credits
UPDATE ship_type SET cost = 21275738 WHERE ship_type_id = 22;
-- Federal Ultimatum to 675 shields
UPDATE ship_type_support_hardware SET max_amount = 675 WHERE ship_type_id = 22 AND hardware_type_id = 1;
-- Federal Ultimatum to 550 armour
UPDATE ship_type_support_hardware SET max_amount = 550 WHERE ship_type_id = 22 AND hardware_type_id = 2;

-- Assasin to 5,483,452 credits
UPDATE ship_type SET cost = 5483452 WHERE ship_type_id = 24;

-- Death Cruiser to 17,890,100 credits
UPDATE ship_type SET cost = 17890100 WHERE ship_type_id = 25;
-- Death Cruiser to 100 cds
UPDATE ship_type_support_hardware SET max_amount = 100 WHERE ship_type_id = 25 AND hardware_type_id = 4;

-- Juggernaut to 1200 armour
UPDATE ship_type_support_hardware SET max_amount = 1200 WHERE ship_type_id = 37 AND hardware_type_id = 2;

-- Rogue to 375 shields
UPDATE ship_type_support_hardware SET max_amount = 375 WHERE ship_type_id = 65 AND hardware_type_id = 1;
-- Rogue to 450 armour
UPDATE ship_type_support_hardware SET max_amount = 450 WHERE ship_type_id = 65 AND hardware_type_id = 2;

-- Border Cruiser to 475 shields
UPDATE ship_type_support_hardware SET max_amount = 475 WHERE ship_type_id = 42 AND hardware_type_id = 1;
-- Border Cruiser to 475 armour
UPDATE ship_type_support_hardware SET max_amount = 475 WHERE ship_type_id = 42 AND hardware_type_id = 2;

-- Blockade Runner to 10 speed
UPDATE ship_type SET speed = 10 WHERE ship_type_id = 66;

-- Deal Maker to 11 speed
UPDATE ship_type SET speed = 11 WHERE ship_type_id = 31;

-- Stellar Freighter to 9 speed
UPDATE ship_type SET speed = 9 WHERE ship_type_id = 6;

-- Thief to 175 holds
UPDATE ship_type_support_hardware SET max_amount = 175 WHERE ship_type_id = 23 AND hardware_type_id = 3;
