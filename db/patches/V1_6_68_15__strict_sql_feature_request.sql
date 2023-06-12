-- Remove unused `feature_request` vote columns
ALTER TABLE `feature_request`
	DROP COLUMN `fav`,
	DROP COLUMN `yes`,
	DROP COLUMN `no`;
