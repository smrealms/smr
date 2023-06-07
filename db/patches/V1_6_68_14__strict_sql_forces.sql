-- Make changes needed to enable strict SQL mode
ALTER TABLE `sector_has_forces`
	MODIFY `refresh_at` int unsigned NOT NULL DEFAULT 0,
	MODIFY `refresher` int unsigned NOT NULL DEFAULT 0;
