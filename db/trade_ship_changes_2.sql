UPDATE smr_new.ship_type SET cost = 4292187 WHERE ship_type_id = 40; -- Ambassador to 4,292,187 credits
UPDATE smr_new.ship_type_support_hardware SET max_amount = 235 WHERE ship_type_id = 40 AND hardware_type_id = 3; -- Ambassador to 235 holds

UPDATE smr_new.ship_type_support_hardware SET max_amount = 300 WHERE ship_type_id = 32 AND hardware_type_id = 3; -- Deep-Spacer to 300 holds

UPDATE smr_new.ship_type_support_hardware SET max_amount = 200 WHERE ship_type_id = 66 AND hardware_type_id = 3; -- Blockade Runner to 200 holds

UPDATE smr_new.ship_type_support_hardware SET max_amount = 200 WHERE ship_type_id = 31 AND hardware_type_id = 3; -- Deal-Maker to 200 holds

UPDATE smr_new.ship_type SET cost = 7508455, speed = 8 WHERE ship_type_id = 6; -- Stellar Freighter to 7,508,455 credits, 8 speed
UPDATE smr_new.ship_type_support_hardware SET max_amount = 225 WHERE ship_type_id = 31 AND hardware_type_id = 3; -- Stellar Freighter to 225 holds

UPDATE smr_new.ship_type SET cost = 9802157 WHERE ship_type_id = 23; -- Thief to 9,802,157 credits
UPDATE smr_new.ship_type_support_hardware SET max_amount = 160 WHERE ship_type_id = 23 AND hardware_type_id = 3; -- Thief to 160 holds

UPDATE smr_new.ship_type SET cost = 7095764 WHERE ship_type_id = 33; -- Trade-Master to 7,095,764 credits
UPDATE smr_new.ship_type_support_hardware SET max_amount = 0 WHERE ship_type_id = 33 AND hardware_type_id = 9; -- Remove IG from Trade-Master
UPDATE smr_new.ship_type_support_hardware SET max_amount = 1 WHERE ship_type_id = 33 AND hardware_type_id = 11; -- Add DCS to Trade-Master

UPDATE smr_new.ship_type_support_hardware SET max_amount = 300 WHERE ship_type_id = 10 AND hardware_type_id = 2; -- Freighter to 300 armour

UPDATE smr_new.ship_type_support_hardware SET max_amount = 355 WHERE ship_type_id = 5 AND hardware_type_id = 1; -- Planetary Trader to 355 shields
UPDATE smr_new.ship_type_support_hardware SET max_amount = 10 WHERE ship_type_id = 5 AND hardware_type_id = 4; -- Planetary Trader to 10 CDs

UPDATE smr_new.ship_type SET cost = 4354955, speed = 12 WHERE ship_type_id = 30; -- Trip-Maker to 4,354,955 credits
UPDATE smr_new.ship_type_support_hardware SET max_amount = 225 WHERE ship_type_id = 30 AND hardware_type_id = 3; -- Trip-Maker to 225 holds
UPDATE smr_new.ship_type_support_hardware SET max_amount = 1 WHERE ship_type_id = 30 AND hardware_type_id = 9; -- Add IG to Trip-Maker

UPDATE smr_new.ship_type_support_hardware SET max_amount = 1 WHERE ship_type_id = 4 AND hardware_type_id = 7; -- Add Scanner to Merchant Vessel
UPDATE smr_new.ship_type_support_hardware SET max_amount = 5 WHERE ship_type_id = 28 AND hardware_type_id = 5; -- Add 5 scouts to Newbie Merchant Vessel

UPDATE smr_new.ship_type_support_hardware SET max_amount = 100 WHERE ship_type_id = 29 AND hardware_type_id = 3; -- Small-Timer to 100 holds

UPDATE smr_new.ship_type SET speed = 7 WHERE ship_type_id = 51; -- Drudge to 7 speed
UPDATE smr_new.ship_type_support_hardware SET max_amount = 260 WHERE ship_type_id = 51 AND hardware_type_id = 3; -- Drudge to 260 holds
UPDATE smr_new.ship_type_support_hardware SET max_amount = 1 WHERE ship_type_id = 51 AND hardware_type_id = 9; -- Add IG to Drudge

UPDATE smr_new.ship_type SET speed = 12 WHERE ship_type_id = 57; -- Expediter to 12 speed
UPDATE smr_new.ship_type_support_hardware SET max_amount = 160 WHERE ship_type_id = 57 AND hardware_type_id = 3; -- Expediter to 160 holds


UPDATE smr_new.ship_type_support_hardware SET max_amount = 255 WHERE ship_type_id = 35 AND hardware_type_id = 3; -- Leviathan to 255 holds

UPDATE smr_new.ship_type_support_hardware SET max_amount = 250 WHERE ship_type_id = 46 AND hardware_type_id = 3; -- Favoured Offspring to 250 holds

UPDATE smr_new.ship_type SET speed = 10 WHERE ship_type_id = 72; -- Vengeance to 10 speed
