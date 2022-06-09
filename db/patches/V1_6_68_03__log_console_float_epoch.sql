-- Convert exact microtime representation using bigint into a double
ALTER TABLE `account_has_logs` MODIFY `microtime` DOUBLE NOT NULL;
UPDATE `account_has_logs` SET `microtime` = `microtime` / 1E6;
