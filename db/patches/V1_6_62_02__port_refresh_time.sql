-- Add a `last_update` field to the `port_has_goods` table
ALTER TABLE port_has_goods ADD COLUMN last_update int(10) unsigned NOT NULL;

-- Copy `last_update` from `port` to `port_has_goods`
UPDATE port_has_goods, port SET port_has_goods.last_update = port.last_update
  WHERE port_has_goods.game_id = port.game_id AND port_has_goods.sector_id = port.sector_id;

-- Remove `last_update` field from the `port` table
ALTER TABLE port DROP COLUMN last_update;
