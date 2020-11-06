-- Add `assists` column to the `player` table.
ALTER TABLE player ADD COLUMN assists smallint unsigned NOT NULL DEFAULT 0 AFTER deaths;
