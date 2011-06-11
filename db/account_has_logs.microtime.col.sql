ALTER TABLE account_has_logs CHANGE time microtime INT(16) UNSIGNED NOT NULL;
UPDATE account_has_logs SET microtime = microtime * 1000000;