-- Add a `warp` field to the `sector` table
ALTER TABLE sector ADD COLUMN warp int(10) unsigned NOT NULL DEFAULT '0' AFTER link_right;

-- Move the warp data into the `sector` table
UPDATE sector JOIN warp USING (game_id) SET warp = sector_id_2 WHERE sector_id = sector_id_1;
UPDATE sector JOIN warp USING (game_id) SET warp = sector_id_1 WHERE sector_id = sector_id_2;
DROP TABLE warp;
