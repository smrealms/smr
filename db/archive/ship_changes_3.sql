UPDATE smr_new.ship_type_support_hardware SET max_amount = 230 WHERE ship_type_id = 30 AND hardware_type_id = 3; -- Trip-Maker to 230 holds

UPDATE smr_new.ship_type_support_hardware SET max_amount = 250 WHERE ship_type_id = 8 AND hardware_type_id IN (1,2); -- Advanced Courier Vessel to 250 shields/armour

UPDATE smr_new.ship_type_support_hardware SET max_amount = 700 WHERE ship_type_id = 36 AND hardware_type_id = 2; -- Goliath to 700 armour
UPDATE smr_new.ship_type_support_hardware SET max_amount = 10 WHERE ship_type_id = 36 AND hardware_type_id = 5; -- Add 10 scouts to Goliath
UPDATE smr_new.ship_type_support_hardware SET max_amount = 10 WHERE ship_type_id = 36 AND hardware_type_id = 6; -- Add 5 mines to Goliath
UPDATE smr_new.ship_type SET hardpoint = 5 WHERE ship_type_id = 36; -- Goliath to 5 hardpoints

UPDATE smr_new.ship_type_support_hardware SET max_amount = 215 WHERE ship_type_id = 73 AND hardware_type_id IN (1,2); -- Retribution to 215 shields/armour
UPDATE smr_new.ship_type SET cost = 3235800, hardpoint = 5, speed = 10 WHERE ship_type_id = 73; -- Retribution to 3,235,800 credits, 5 hardpoints and 10 speed

UPDATE smr_new.ship_type_support_hardware SET max_amount = 415 WHERE ship_type_id = 53 AND hardware_type_id = 1; -- Predator to 415 shields
UPDATE smr_new.ship_type_support_hardware SET max_amount = 375 WHERE ship_type_id = 53 AND hardware_type_id = 2; -- Predator to 375 armour
UPDATE smr_new.ship_type_support_hardware SET max_amount = 15 WHERE ship_type_id = 53 AND hardware_type_id = 5; -- Predator to 15 scouts
UPDATE smr_new.ship_type SET hardpoint = 5, speed = 10 WHERE ship_type_id = 53; -- Predator to 5 hardpoints and 10 speed

UPDATE smr_new.ship_type_support_hardware SET max_amount = 250 WHERE ship_type_id = 59 AND hardware_type_id IN (1,2); -- Bounty Hunter to 250 shields/armour
UPDATE smr_new.ship_type SET cost = 2568634, hardpoint = 5, speed = 13 WHERE ship_type_id = 59; -- Bounty Hunter to 2,568,634 credits, 5 hardpoints and 13 speed

UPDATE smr_new.ship_type SET cost = 6574860, hardpoint = 5 WHERE ship_type_id = 65; -- Rogue to 6,574,860 credits and 5 hardpoints

UPDATE smr_new.ship_type SET hardpoint = 5 WHERE ship_type_id = 42; -- Border Cruiser to 5 hardpoints

UPDATE smr_new.ship_type_support_hardware SET max_amount = 700 WHERE ship_type_id = 48 AND hardware_type_id = 1; -- Advanced Carrier to 700 shields

UPDATE smr_new.ship_type SET cost = 12719505 WHERE ship_type_id = 43; -- Destroyer to 12,719,505 credits

UPDATE smr_new.ship_type SET cost = 13724001 WHERE ship_type_id = 75; -- Fury to 13,724,001 credits

UPDATE smr_new.ship_type SET cost = 11088764 WHERE ship_type_id = 67; -- Dark Mirage to 11,088,764 credits

UPDATE smr_new.ship_type SET cost = 38675738 WHERE ship_type_id = 22; -- Federal Ultimatum to 38,675,738 credits