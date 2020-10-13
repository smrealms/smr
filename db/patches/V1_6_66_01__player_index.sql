-- Make player_id the index of player-related tables, not index_id
ALTER TABLE player_attacks_planet RENAME COLUMN account_id TO player_id;
UPDATE player_attacks_planet SET player_id = (SELECT player_id FROM account JOIN player USING (account_id) WHERE game_id = player_attacks_planet.game_id AND account_id = player_attacks_planet.player_id);

ALTER TABLE player_attacks_port RENAME COLUMN account_id TO player_id;
UPDATE player_attacks_port SET player_id = (SELECT player_id FROM account JOIN player USING (account_id) WHERE game_id = player_attacks_port.game_id AND account_id = player_attacks_port.player_id);

ALTER TABLE player_can_fed RENAME COLUMN account_id TO player_id;
UPDATE player_can_fed SET player_id = (SELECT player_id FROM account JOIN player USING (account_id) WHERE game_id = player_can_fed.game_id AND account_id = player_can_fed.player_id);

ALTER TABLE player_has_alliance_role RENAME COLUMN account_id TO player_id;
UPDATE player_has_alliance_role SET player_id = (SELECT player_id FROM account JOIN player USING (account_id) WHERE game_id = player_has_alliance_role.game_id AND account_id = player_has_alliance_role.player_id);

alliance_thread sender_id -> player_id
galactic_post_article writer_id -> player_id
planet owner_id -> player_id
sector_has_forces owner_id -> player_id
alliance_bank_transactions payee_id -> player_id
anon_bank owner_id -> player_id
