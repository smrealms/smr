--Merchant Vessel -20  holds (170 -> 150).  Trade throughput down from 1530 (9tph * 170 cargo holds) to 1350 (9tph * 150 cargo holds).
UPDATE ship_type_support_hardware SET max_amount = 150 WHERE ship_type_id = 4 AND hardware_type_id = 3;

--Light Courier Vessel +60 holds (120 -> 180).  TTP +600 (1200 -> 1800).
UPDATE ship_type_support_hardware SET max_amount = 150 WHERE ship_type_id = 7 AND hardware_type_id = 3;

--Freighter +25 holds (250 -> 275).  TTP +250 (2500 -> 2750).
UPDATE ship_type_support_hardware SET max_amount = 275 WHERE ship_type_id = 15 AND hardware_type_id = 3;

--Thief +15 holds (160 -> 175).  TTP + (1920 -> 2100).
UPDATE ship_type_support_hardware SET max_amount = 175 WHERE ship_type_id = 23 AND hardware_type_id = 3;

--Thief Hunter -> Trader
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 23;

--Trade-Master +30 holds (200 -> 255).  TTP +495 (1800 -> 2295).
UPDATE ship_type_support_hardware SET max_amount = 255 WHERE ship_type_id = 33 AND hardware_type_id = 3;

--Leviathan -40 holds (240 -> 200 ).  TTP -280 (1680 -> 1400).
UPDATE ship_type_support_hardware SET max_amount = 200 WHERE ship_type_id = 35 AND hardware_type_id = 3; 

--Ambassador +40 holds (150 -> 190).  TTP +360 (1350 -> 1710).
UPDATE ship_type_support_hardware SET max_amount = 190 WHERE ship_type_id = 40 AND hardware_type_id = 3; 

--Renaissance +60 holds (90 -> 150).  TTP +480 (720 -> 1200).
UPDATE ship_type_support_hardware SET max_amount = 150 WHERE ship_type_id = 41 AND hardware_type_id = 3; 

--Favored Offspring +30 holds (200 -> 230).  TTP +240 (1600 -> 1840).
UPDATE ship_type_support_hardware SET max_amount = 230 WHERE ship_type_id = 46 AND hardware_type_id = 3; 

--Proto Carrier +10 holds  (120 -> 130).  TTP +90 (1080 -> 1170).
UPDATE ship_type_support_hardware SET max_amount = 130 WHERE ship_type_id = 47 AND hardware_type_id = 3; 

--Expediter -10 holds (180 -> 170).  TTP -90 (1620 -> 1530).
UPDATE ship_type_support_hardware SET max_amount = 170 WHERE ship_type_id = 57 AND hardware_type_id = 3; 

--Negotiator +120 holds.  TTP +1320 (440 -> 1760).
UPDATE ship_type_support_hardware SET max_amount = 170 WHERE ship_type_id = 57 AND hardware_type_id = 3;

--Negotiator +1,397,104 cost (155,234 -> 1,552,340).
UPDATE ship_type SET cost = 1552340 WHERE ship_type_id = 57;

--Blockade Runner +40 holds (175 -> 215).  TTP +360 (1575 -> 1935)
UPDATE ship_type_support_hardware SET max_amount = 215 WHERE ship_type_id = 66 AND hardware_type_id = 3;

--Vengeance +20 holds (170 -> 190).  TTP +160 (1360 -> 1520).
UPDATE ship_type_support_hardware SET max_amount = 190 WHERE ship_type_id = 72 AND hardware_type_id = 3;

