/* adding in colour to account table */

ALTER TABLE account 
ADD friendly_colour CHAR(6) NOT NULL DEFAULT '00AA00';
ALTER TABLE account 
ADD neutral_colour CHAR(6) NOT NULL DEFAULT 'FFD800';
ALTER TABLE account 
ADD enemy_colour CHAR(6) NOT NULL DEFAULT 'FF0000';
