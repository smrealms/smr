-- Add an `npc` field to the `player` table
ALTER TABLE player ADD COLUMN npc enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE';

-- Set `npc` to true for any existing NPC players
UPDATE player, account SET player.npc = 'TRUE'
  WHERE account.login IN (SELECT login FROM npc_logins)
    AND player.account_id = account.account_id;
