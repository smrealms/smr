ALTER TABLE alliance_has_roles ADD positive_balance ENUM( 'TRUE', 'FALSE' ) NOT NULL DEFAULT 'FALSE' AFTER with_per_day;
UPDATE alliance_has_roles SET positive_balance = 'TRUE' WHERE with_per_day = -1;
UPDATE alliance_has_roles SET with_per_day = 0 WHERE with_per_day = -1;
UPDATE alliance_has_roles SET with_per_day = -1 WHERE with_per_day = -2;