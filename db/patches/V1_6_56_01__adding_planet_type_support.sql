CREATE TABLE IF NOT EXISTS `planet_type` (
	`planet_type_id` int(10) unsigned AUTO_INCREMENT PRIMARY KEY,
	`planet_type_name` varchar( 100 ),
	`planet_type_description` varchar( 100 ),
	`planet_image_link` varchar( 100 ),
	`planet_can_construction` 	enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
	`planet_can_defense` 		enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
	`planet_can_financial` 		enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
	`planet_can_stockpile` 		enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
	`planet_can_ownership` 		enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
	`planet_can_armory` 		enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE',
	`planet_can_hangar` 		enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE'
	
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `planet_type` (`planet_type_id`, `planet_type_name`, `planet_type_description`, `planet_image_link`, `planet_can_construction`, `planet_can_defense`, `planet_can_financial`, `planet_can_stockpile`, `planet_can_ownership`, `planet_can_armory`, `planet_can_hangar`) VALUES
(NULL, 'Terran Planet', 'A lush world, with forests, seas, sweeping meadows, and indigenous lifeforms', 'images/planet1.png', 'TRUE', 'TRUE', 'TRUE', 'TRUE', 'TRUE', 'FALSE', 'FALSE'),
(NULL, 'Arid Planet', 'A world mostly devoid of surface water, but capable of supporting life', 'images/planet2.png', 'TRUE', 'TRUE', 'FALSE', 'TRUE', 'TRUE', 'TRUE', 'TRUE');

CREATE TABLE IF NOT EXISTS `planet_can_build` (
	`planet_type_id` int(10) unsigned NOT NULL DEFAULT '0',
	`construction_id` int(10) unsigned NOT NULL DEFAULT '0',
	`max_amount` int(10) unsigned NOT NULL DEFAULT '0',
	`cost_time` int(10) unsigned NOT NULL DEFAULT '0',
	`exp_gain` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `planet`
ADD `planet_type_id` int(10) unsigned NOT NULL DEFAULT '1';

ALTER TABLE `planet_construction`
MODIFY `construction_description` VARCHAR( 100 );
UPDATE `planet_construction`
SET `construction_description` = 'Increases planet&rsquo;s maximum shield capacity by 100 shields' WHERE `construction_id` = 1;
UPDATE `planet_construction`
SET `construction_description` = 'Increases planet&rsquo;s maximum drone capacity by 20 drones' WHERE `construction_id` = 2;
UPDATE `planet_construction`
SET `construction_description` = 'Builds a turret capable of dealing 250 damage to enemy ships when fired on' WHERE `construction_id` = 3;

ALTER TABLE `planet`
ADD `armour` int(10) unsigned NOT NULL DEFAULT '0' AFTER shields;

INSERT INTO `planet_construction` (`construction_id`, `construction_name`, `construction_description`, `max_construction`, `exp_gain`) VALUES 
(4, 'Bunker', 'Increases planet&rsquo;s maximum armour capacity by 100 armour', 0, 90);

INSERT INTO `planet_can_build` (`planet_type_id`, `construction_id`, `max_amount`, `cost_time`, `exp_gain`) VALUES 
('1', '1', '25', '10000', '90'),
('1', '2', '100', '10000', '180'),
('1', '3', '10', '10000', '540'),
('1', '4', '0', '10000', '90'),
('2', '1', '25', '10000', '90'),
('2', '2', '0', '10000', '180'),
('2', '3', '15', '10000', '540'),
('2', '4', '25', '10000', '90');