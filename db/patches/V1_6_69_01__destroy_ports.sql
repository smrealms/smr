-- Add boolean property `game.destroy_ports`
ALTER TABLE `game`
	ADD COLUMN `destroy_ports` enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE';
