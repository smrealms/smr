-- Trade-Master to 7 speed, 345 holds.
UPDATE smr_new.ship_type SET speed = 7 WHERE ship_type_id = 33;
UPDATE smr_new.ship_type_support_hardware SET max_amount = 345 WHERE ship_type_id = 33 AND hardware_type_id = 3;