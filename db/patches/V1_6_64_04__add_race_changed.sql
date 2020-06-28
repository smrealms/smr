-- Add `race_changed` column to `player` table
ALTER TABLE player ADD COLUMN race_changed enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE' AFTER name_changed;
