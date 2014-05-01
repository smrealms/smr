/* adding in color to account table */

ALTER TABLE account 
ADD friendly_color CHAR(6) NOT NULL DEFAULT '00AA00';
ALTER TABLE account 
ADD neutral_color CHAR(6) NOT NULL DEFAULT 'FFD800';
ALTER TABLE account 
ADD enemy_color CHAR(6) NOT NULL DEFAULT 'FF0000';
