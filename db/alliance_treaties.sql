ALTER TABLE alliance_treaties
CHANGE trader_assist	trader_assist	ENUM( 'TRUE', 'FALSE' ) NOT NULL,
CHANGE trader_defend	trader_defend	ENUM( 'TRUE', 'FALSE' ) NOT NULL,
CHANGE trader_nap		trader_nap		ENUM( 'TRUE', 'FALSE' ) NOT NULL,
CHANGE raid_assist		raid_assist		ENUM( 'TRUE', 'FALSE' ) NOT NULL,
CHANGE planet_nap		planet_nap		ENUM( 'TRUE', 'FALSE' ) NOT NULL,
CHANGE planet_land		planet_land		ENUM( 'TRUE', 'FALSE' ) NOT NULL,
CHANGE forces_nap		forces_nap		ENUM( 'TRUE', 'FALSE' ) NOT NULL,
CHANGE aa_access		aa_access		ENUM( 'TRUE', 'FALSE' ) NOT NULL,
CHANGE mb_read			mb_read			ENUM( 'TRUE', 'FALSE' ) NOT NULL,
CHANGE mb_write			mb_write		ENUM( 'TRUE', 'FALSE' ) NOT NULL,
CHANGE mod_read			mod_read		ENUM( 'TRUE', 'FALSE' ) NOT NULL;