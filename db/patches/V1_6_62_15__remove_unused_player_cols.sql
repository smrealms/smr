-- Remove unused columns in the `player` table
ALTER TABLE player DROP COLUMN controlled,
                   DROP COLUMN sector_change,
                   DROP COLUMN safe_exit,
                   DROP COLUMN detected,
                   DROP COLUMN zoom_on,
                   DROP COLUMN out_of_game;
