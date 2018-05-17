-- Change "Newbie Merchant Vessel" ship class from Hunter to Starter.
UPDATE ship_type SET ship_class_id = 5 WHERE ship_type_id = 28;
