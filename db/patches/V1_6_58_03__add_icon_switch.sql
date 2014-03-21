/* modify account to add icon switch */

ALTER TABLE account 
ADD override_icons ENUM('TRUE', 'FALSE') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'FALSE';

