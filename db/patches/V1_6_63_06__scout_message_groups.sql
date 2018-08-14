-- Add `group_scout_messages` column to the `player` table.
ALTER TABLE player ADD COLUMN group_scout_messages enum('NEVER','AUTO','ALWAYS') NOT NULL DEFAULT 'AUTO' AFTER force_drop_messages;
