-- Remove King of the Hill stats from alliance table
ALTER TABLE `alliance`
	DROP COLUMN `hill_summit_cumulative`,
	DROP COLUMN `hill_heights_cumulative`,
	DROP COLUMN `hill_foothills_cumulative`,
	DROP COLUMN `hill_kills`,
	DROP COLUMN `hill_points`;
