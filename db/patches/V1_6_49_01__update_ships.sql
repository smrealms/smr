-- Bounty hunter to 7 hardpoints
UPDATE ship_type SET hardpoint = 7 WHERE ship_type_id = 59;

-- Celestial Combatant to 2,656,949 credits and 7 hardpoints
UPDATE ship_type SET cost = 2656949, hardpoint = 7, speed = 13 WHERE ship_type_id = 19;

-- Goliath to 2,580,866 credits and 7 hardpoints
UPDATE ship_type SET cost = 2580866, hardpoint = 7 WHERE ship_type_id = 36;

-- Predator to 2,623,300 credits and 7 hardpoints
UPDATE ship_type SET cost = 2623300, hardpoint = 7 WHERE ship_type_id = 53;

-- Resistance to 7 hardpoints
UPDATE ship_type SET hardpoint = 7 WHERE ship_type_id = 64;

-- Retribution to 2,235,800 credits and 7 hardpoints
UPDATE ship_type SET cost = 2235800, hardpoint = 7 WHERE ship_type_id = 73;

-- Proto Carrier to 2 hardpoints and 360 CDs
UPDATE ship_type SET hardpoint = 2 WHERE ship_type_id = 47;
UPDATE ship_type_support_hardware SET max_amount = 360 WHERE ship_type_id = 47 AND hardware_type_id = 4;

-- Assasin to 2,883,452 credits and 7 hardpoints
UPDATE ship_type SET cost = 2883452, hardpoint = 7 WHERE ship_type_id = 24;

-- Celestial Mercenary to 7 hardpoints and 20 scouts
UPDATE ship_type SET hardpoint = 7 WHERE ship_type_id = 18;
UPDATE ship_type_support_hardware SET max_amount = 360 WHERE ship_type_id = 18 AND hardware_type_id = 5;

-- Federal Warrant to 3,026,598 credits and 7 hardpoints
UPDATE ship_type SET cost = 3026598, hardpoint = 7 WHERE ship_type_id = 21;

-- Retaliation to 235,900 credits and 12 hardpoints
UPDATE ship_type SET cost = 235900, hardpoint = 12 WHERE ship_type_id = 71;

-- Federal Discovery to 1,335,689 credits and 4 hardpoints
UPDATE ship_type SET cost = 1335689, hardpoint = 4 WHERE ship_type_id = 20;

-- Juggernaut to 5,404,130 credits, 6 hardpoints and 1300 armour
UPDATE ship_type SET cost = 5404130, hardpoint = 6 WHERE ship_type_id = 37;
UPDATE ship_type_support_hardware SET max_amount = 1300 WHERE ship_type_id = 37 AND hardware_type_id = 2;

-- Vindicator to 6,400,000 credits
UPDATE ship_type SET cost = 6400000 WHERE ship_type_id = 74;

-- Assault Craft to 18,487,328 credits
UPDATE ship_type SET cost = 18487328 WHERE ship_type_id = 61;

-- Dark Mirage to 18,660,955 credits
UPDATE ship_type SET cost = 18660955 WHERE ship_type_id = 67;

-- Destroyer to 18,099,381 credits
UPDATE ship_type SET cost = 18099381 WHERE ship_type_id = 43;

-- Devastator to 18,318,853 credits
UPDATE ship_type SET cost = 18318853 WHERE ship_type_id = 38;

-- Eater of Souls to 18,572,719 credits
UPDATE ship_type SET cost = 18572719 WHERE ship_type_id = 55;

-- Mother Ship to 12,932,759 credits
UPDATE ship_type SET cost = 12932759 WHERE ship_type_id = 49;

-- Fury to 12,932,759 credits and 900 armour
UPDATE ship_type SET cost = 17655001 WHERE ship_type_id = 75;
UPDATE ship_type_support_hardware SET max_amount = 900 WHERE ship_type_id = 75 AND hardware_type_id = 2;

-- Freighter to 5,791,393 credits
UPDATE ship_type SET cost = 5791393 WHERE ship_type_id = 10;

-- Planetary Freighter to 6,115,028 credits
UPDATE ship_type SET cost = 6115028 WHERE ship_type_id = 11;

-- Planetary Super Freighter to 6,235,792 credits
UPDATE ship_type SET cost = 6235792 WHERE ship_type_id = 12;

-- Planetary Trader to 5,983,335 credits
UPDATE ship_type SET cost = 5983335 WHERE ship_type_id = 5;

-- Trade-Master to 5,095,764 credits
UPDATE ship_type SET cost = 5095764 WHERE ship_type_id = 33;

-- Stellar Freighter to 7,008,455 credits
UPDATE ship_type SET cost = 7008455 WHERE ship_type_id = 6;

-- Thief to 7,802,157 credits
UPDATE ship_type SET cost = 7802157 WHERE ship_type_id = 23;

-- Advanced Courier Vessel to 5,560,740 credits
UPDATE ship_type SET cost = 5560740 WHERE ship_type_id = 8;

-- Inter-Stellar Trader to 5,914,159 credits
UPDATE ship_type SET cost = 5914159 WHERE ship_type_id = 9;
