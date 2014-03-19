/* modify news type enum */

ALTER TABLE news 
MODIFY COLUMN type ENUM( 'breaking', 'regular', 'lotto', 'planet', 'port', 'combat', 'forces') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'regular';
