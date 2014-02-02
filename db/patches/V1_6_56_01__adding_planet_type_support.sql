CREATE TABLE IF NOT EXISTS `planet_type` (
	`planet_type_id` int(10) unsigned AUTO_INCREMENT PRIMARY KEY,
	`planet_type_name` varchar( 100 ),
	`planet_type_description` varchar( 100 ),
	`planet_image_link` varchar( 100 ),
	`planet_max_attackers` int(10) unsigned NOT NULL DEFAULT '10',
	`planet_max_landed` int(10) unsigned NOT NULL DEFAULT '0';
	
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `planet_type` (`planet_type_id`, `planet_type_name`, `planet_type_description`, `planet_image_link`, `planet_max_attackers`, `planet_max_landed`) VALUES
(NULL, 'Terran Planet', 'A lush world, with forests, seas, sweeping meadows, and indigenous lifeforms', 'images/planet1.png', '10', '0'),
(NULL, 'Arid Planet', 'A world mostly devoid of surface water, but capable of supporting life', 'images/planet2.png', '5', '5');

CREATE TABLE IF NOT EXISTS `planet_type_has_section` (
	planet_type_id int(10) unsigned NOT NULL,
	planet_section ENUM('CONSTRUCTION', 'DEFENSE', 'FINANCE', 'STOCKPILE', 'OWNERSHIP') NOT NULL,
	PRIMARY KEY (planet_type_id, planet_section)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `planet_type_has_section` (`planet_type_id`, `planet_section`) VALUES
('1', 'CONSTRUCTION'),
('1', 'DEFENSE'),
('1', 'FINANCE'),
('1', 'STOCKPILE'),
('1', 'OWNERSHIP'),
('2', 'CONSTRUCTION'),
('2', 'DEFENSE'),
('2', 'STOCKPILE'),
('2', 'OWNERSHIP');

CREATE TABLE IF NOT EXISTS `planet_can_build` (
	`planet_type_id` int(10) unsigned NOT NULL DEFAULT '0',
	`construction_id` int(10) unsigned NOT NULL DEFAULT '0',
	`max_amount` int(10) unsigned NOT NULL DEFAULT '0',
	`cost_time` int(10) unsigned NOT NULL DEFAULT '0',
	`cost_credit` int(10) unsigned NOT NULL DEFAULT '0',
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

ALTER TABLE `planet_construction`
ADD `construction_image` varchar( 100 ) AFTER construction_description;
UPDATE `planet_construction`
SET `construction_image` = 'images/generator.png' WHERE `construction_id` = 1;
UPDATE `planet_construction`
SET `construction_image` = 'images/hangar.png' WHERE `construction_id` = 2;
UPDATE `planet_construction`
SET `construction_image` = 'images/turret.png' WHERE `construction_id` = 3;

ALTER TABLE `planet`
ADD `armour` int(10) unsigned NOT NULL DEFAULT '0' AFTER shields;

INSERT INTO `planet_construction` (`construction_id`, `construction_name`, `construction_description`, `construction_image`, `max_construction`, `exp_gain`) VALUES 
(4, 'Bunker', 'Increases planet&rsquo;s maximum armour capacity by 100 armour', 'images/bunker.png', 0, 90);

INSERT INTO `planet_can_build` (`planet_type_id`, `construction_id`, `max_amount`, `cost_time`, `cost_credit`, `exp_gain`) VALUES 
('1', '1', '25', '10800', '100000', '90'),
('1', '2', '100', '21600', '100000', '180'),
('1', '3', '10', '64800', '1000000', '540'),
('1', '4', '0', '10000', '50000', '90'),
('2', '1', '25', '10800', '100000', '90'),
('2', '2', '0', '10800', '100000', '180'),
('2', '3', '15', '21600', '750000', '180'),
('2', '4', '25', '10800', '50000', '90');

INSERT INTO `planet_cost_good` (`construction_id`, `good_id`, `amount`) VALUES 
('4', '3', '35'),
('4', '1', '20'),
('4', '8', '15');



DROP TABLE `planet_cost_time`;
DROP TABLE `planet_cost_credits`;