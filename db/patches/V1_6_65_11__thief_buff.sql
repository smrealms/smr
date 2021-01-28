-- Thief to 170 holds
UPDATE ship_type_support_hardware SET max_amount = 170 WHERE ship_type_id = 23 AND hardware_type_id = 3;
-- Thief to trader class
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 23;
