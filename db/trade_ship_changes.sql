UPDATE smr_new.ship_type SET cost = 5283335 WHERE ship_type_id = 5; -- Planetary Trader to 5,283,335 credits
UPDATE smr_new.ship_type_support_hardware SET max_amount = 300 WHERE ship_type_id = 5 AND hardware_type_id = 3; -- Planetary Trader to 300 holds

UPDATE smr_new.ship_type SET cost = 4560740 WHERE ship_type_id = 8; -- Advanced Courier Vessel to 4,560,740 credits
UPDATE smr_new.ship_type_support_hardware SET max_amount = 235 WHERE ship_type_id = 8 AND hardware_type_id = 3; -- Advanced Courier Vessel to 235 holds
UPDATE smr_new.ship_type_support_hardware SET max_amount = 235 WHERE ship_type_id = 8 AND hardware_type_id IN (1,2); -- Advanced Courier Vessel to 225 shields/armour

UPDATE smr_new.ship_type SET cost = 5314159 WHERE ship_type_id = 9; -- Inter-Stellar Trader to 5,314,159 credits

UPDATE smr_new.ship_type SET cost = 4791393, speed = 10 WHERE ship_type_id = 10; -- Freighter to 4,791,393 credits, 10 speed
UPDATE smr_new.ship_type_support_hardware SET max_amount = 250 WHERE ship_type_id = 10 AND hardware_type_id = 3; -- Freighter to 250 holds
UPDATE smr_new.ship_type_support_hardware SET max_amount = 325 WHERE ship_type_id = 10 AND hardware_type_id = 2; -- Freighter to 325 armour
UPDATE smr_new.ship_type_support_hardware SET max_amount = 0 WHERE ship_type_id = 10 AND hardware_type_id IN(5,6); -- Freighter to 0 scouts/mines

UPDATE smr_new.ship_type SET cost = 6215028 WHERE ship_type_id = 11; -- Planetary Freighter to 6,215,028 credits

UPDATE smr_new.ship_type SET cost = 7035792 WHERE ship_type_id = 12; -- Planetary Super Freighter to 7,035,792 credits
UPDATE smr_new.ship_type_support_hardware SET max_amount = 425 WHERE ship_type_id = 12 AND hardware_type_id = 3; -- Planetary Super Freighter to 425 holds