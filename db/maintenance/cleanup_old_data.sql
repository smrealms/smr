DELETE FROM combat_logs WHERE game_id IN ( SELECT game_id FROM game WHERE end_date < UNIX_TIMESTAMP() );
DELETE FROM message WHERE game_id IN ( SELECT game_id FROM game WHERE end_date < UNIX_TIMESTAMP() );
DELETE FROM player_visited_port WHERE game_id IN ( SELECT game_id FROM game WHERE end_date < UNIX_TIMESTAMP() );
DELETE FROM player_visited_sector WHERE game_id IN ( SELECT game_id FROM game WHERE end_date < UNIX_TIMESTAMP() );
DELETE FROM port_info_cache WHERE game_id IN ( SELECT game_id FROM game WHERE end_date < UNIX_TIMESTAMP() );
DELETE FROM player_has_unread_messages WHERE game_id IN ( SELECT game_id FROM game WHERE end_date < UNIX_TIMESTAMP() );

DELETE FROM npc_logs;
