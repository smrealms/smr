ALTER TABLE player_has_mission
CHANGE next_step unread ENUM('TRUE', 'FALSE') NOT NULL;
