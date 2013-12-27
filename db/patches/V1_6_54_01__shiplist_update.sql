/*Support for ship classes*/
CREATE TABLE IF NOT EXISTS `ship_class` (
	`ship_class_id` int(10) unsigned AUTO_INCREMENT PRIMARY KEY,
	`ship_class_name` varchar(100)  NOT NULL DEFAULT 'NO CLASS'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* so far I know of only 5 classes */
INSERT INTO `ship_class` (`ship_class_id`, `ship_class_name`) VALUES 
(1, 'Hunter'),
(2, 'Trader'),
(3, 'Raider'),
(4, 'Scout'),
(5, 'Starter');

ALTER TABLE `ship_type` 
ADD `ship_class_id` int(10) unsigned NOT NULL DEFAULT '1' AFTER `race_id`;
/* Set class for all the ships */

#Galactic Semi
UPDATE ship_type st SET st.ship_class_id = 5 WHERE st.ship_type_id = 1;
#Armoured Semi
UPDATE ship_type st SET st.ship_class_id = 5 WHERE st.ship_type_id = 2;
#Celestial Trader
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 3;
#Merchant Vessel
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 4;
#Planetary Trader
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 5;
#Stellar Freighter
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 6;
#Light Courier Vessel
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 7;
#Advanced Courier Vessel
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 8;
#Inter-Stellar Trader
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 9;
#Freighter
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 10;
#Planetary Freighter
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 11;
#Planetary Super Freighter
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 12;
#Unarmed Scout
UPDATE ship_type st SET st.ship_class_id = 4 WHERE st.ship_type_id = 13;
#Small Escort
UPDATE ship_type st SET st.ship_class_id = 5 WHERE st.ship_type_id = 14;
#Light Cruiser
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 15;
#Medium Cruiser
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 16;
#Battle Cruiser
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 17;
#Celestial Mercenary
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 18;
#Celestial Combatant
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 19;
#Federal Discovery
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 20;
#Federal Warrant
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 21;
#Federal Ultimatum
UPDATE ship_type st SET st.ship_class_id = 3 WHERE st.ship_type_id = 22;
#Thief
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 23;
#Assassin
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 24;
#Death Cruiser
UPDATE ship_type st SET st.ship_class_id = 3 WHERE st.ship_type_id = 25;
#Light Carrier
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 26;
#Medium Carrier
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 27;
#Newbie Merchant Vessel
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 28;
#Small-Timer
UPDATE ship_type st SET st.ship_class_id = 5 WHERE st.ship_type_id = 29;
#Trip-Maker
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 30;
#Deal-Maker
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 31;
#Deep-Spacer
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 32;
#Trade-Master
UPDATE ship_type st SET st.ship_class_id = 3 WHERE st.ship_type_id = 33;
#Medium Cargo Hulk
UPDATE ship_type st SET st.ship_class_id = 5 WHERE st.ship_type_id = 34;
#Leviathan
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 35;
#Goliath
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 36;
#Juggernaut
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 37;
#Devastator
UPDATE ship_type st SET st.ship_class_id = 3 WHERE st.ship_type_id = 38;
#Light Freighter
UPDATE ship_type st SET st.ship_class_id = 5 WHERE st.ship_type_id = 39;
#Ambassador
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 40;
#Renaissance
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 41;
#Border Cruiser
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 42;
#Destroyer
UPDATE ship_type st SET st.ship_class_id = 3 WHERE st.ship_type_id = 43;
#Tiny Delight
UPDATE ship_type st SET st.ship_class_id = 5 WHERE st.ship_type_id = 44;
#Rebellious Child
UPDATE ship_type st SET st.ship_class_id = 4 WHERE st.ship_type_id = 45;
#Favoured Offspring
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 46;
#Proto Carrier
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 47;
#Advanced Carrier
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 48;
#Mother Ship
UPDATE ship_type st SET st.ship_class_id = 3 WHERE st.ship_type_id = 49;
#A Hatchling`s Due
UPDATE ship_type st SET st.ship_class_id = 5 WHERE st.ship_type_id = 50;
#Drudge
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 51;
#Watchful Eye
UPDATE ship_type st SET st.ship_class_id = 4 WHERE st.ship_type_id = 52;
#Predator
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 53;
#Ravager
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 54;
#Eater of Souls
UPDATE ship_type st SET st.ship_class_id = 3 WHERE st.ship_type_id = 55;
#Swift Venture
UPDATE ship_type st SET st.ship_class_id = 5 WHERE st.ship_type_id = 56;
#Expediter
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 57;
#Star Ranger
UPDATE ship_type st SET st.ship_class_id = 4 WHERE st.ship_type_id = 58;
#Bounty Hunter
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 59;
#Carapace
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 60;
#Assault Craft
UPDATE ship_type st SET st.ship_class_id = 3 WHERE st.ship_type_id = 61;
#Slip Freighter
UPDATE ship_type st SET st.ship_class_id = 5 WHERE st.ship_type_id = 62;
#Negotiator
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 63;
#Resistance
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 64;
#Rogue
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 65;
#Blockade Runner
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 66;
#Dark Mirage
UPDATE ship_type st SET st.ship_class_id = 3 WHERE st.ship_type_id = 67;
#Spooky Midnight Special
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 68;
#Escape Pod
UPDATE ship_type st SET st.ship_class_id = 4 WHERE st.ship_type_id = 69;
#Redeemer
UPDATE ship_type st SET st.ship_class_id = 5 WHERE st.ship_type_id = 70;
#Retaliation
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 71;
#Vengeance
UPDATE ship_type st SET st.ship_class_id = 2 WHERE st.ship_type_id = 72;
#Retribution
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 73;
#Vindicator
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 74;
#Fury
UPDATE ship_type st SET st.ship_class_id = 3 WHERE st.ship_type_id = 75;
#Slayer
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 100;
#Demonica
UPDATE ship_type st SET st.ship_class_id = 1 WHERE st.ship_type_id = 666;
