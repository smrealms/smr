UPDATE smr_new.ship_type_support_hardware SET max_amount = 300 WHERE ship_type_id = 5 AND hardware_type_id = 3; -- Planetary Trader to 300 holds

UPDATE smr_new.ship_type_support_hardware SET max_amount = 235 WHERE ship_type_id = 8 AND hardware_type_id = 3; -- Advanced Courier Vessel to 235 holds
UPDATE smr_new.ship_type_support_hardware SET max_amount = 235 WHERE ship_type_id = 8 AND hardware_type_id IN (1,2); -- Advanced Courier Vessel to 225 shields/armour

UPDATE smr_new.ship_type SET speed = 10 WHERE ship_type_id = 10; -- Freighter to 10 speed
UPDATE smr_new.ship_type_support_hardware SET max_amount = 250 WHERE ship_type_id = 10 AND hardware_type_id = 3; -- Freighter to 250 holds
UPDATE smr_new.ship_type_support_hardware SET max_amount = 350 WHERE ship_type_id = 10 AND hardware_type_id = 2; -- Freighter to 350 armour
UPDATE smr_new.ship_type_support_hardware SET max_amount = 0 WHERE ship_type_id = 10 AND hardware_type_id IN(5,6); -- Freighter to 0 scouts/mines

UPDATE smr_new.ship_type_support_hardware SET max_amount = 0 WHERE ship_type_id = 12 AND hardware_type_id = 3; -- Planetary Super Freighter to 425 holds