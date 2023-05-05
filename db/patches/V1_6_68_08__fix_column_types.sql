-- Convert nullable columns to not null
ALTER TABLE `planet_has_cargo` MODIFY `amount` smallint unsigned NOT NULL;
ALTER TABLE `ship_has_cargo` MODIFY `amount` smallint unsigned NOT NULL;

-- Convert int column to float
ALTER TABLE `weighted_random` MODIFY `weighting` float NOT NULL;
