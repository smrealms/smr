-- Trade-Master to 7 speed, 345 holds.
UPDATE ship_type SET speed = 7 WHERE ship_type_id = 33;
UPDATE ship_type_support_hardware SET max_amount = 345 WHERE ship_type_id = 33 AND hardware_type_id = 3;