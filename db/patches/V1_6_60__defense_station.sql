ALTER TABLE `planet_type` CHANGE `planet_type_description` `planet_type_description` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
INSERT INTO planet_type (planet_type_id, planet_type_name, planet_type_description, planet_image_link, planet_max_attackers, planet_max_landed) VALUES
(4, 
'Defense World', 
'A fully armed and operational battle station loaded with excessive firepower. Attack at your own risk.', 
'images/planet4.png', 
'10', 
'0');

INSERT INTO planet_type_has_section (planet_type_id, planet_section) VALUES
('4', 'CONSTRUCTION'),
('4', 'DEFENSE'),
('4', 'FINANCE'),
('4', 'STOCKPILE'),
('4', 'OWNERSHIP');

INSERT INTO planet_can_build (planet_type_id, construction_id, max_amount, cost_time, cost_credit, exp_gain) VALUES 
('4', '1', '800', '2700', '500', '9'),
('4', '2', '3500', '5400', '500', '18'),
('4', '3', '550', '18200', '5000', '54'),
('4', '4', '500', '2500', '250', '9');
