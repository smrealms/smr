-- Add op_leader permission so it's not limited to leader
ALTER TABLE alliance_has_roles ADD COLUMN op_leader enum('TRUE','FALSE') NOT NULL DEFAULT 'FALSE';

-- Retain op_leader permission for leaders in existing games
UPDATE alliance_has_roles SET op_leader = 'TRUE' WHERE role_id = 1;
