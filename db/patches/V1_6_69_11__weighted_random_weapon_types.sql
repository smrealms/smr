-- Add additional types to fix weapon overlap between combatants.
ALTER TABLE `weighted_random`
	MODIFY `type` enum('WEAPON', 'PLANETWEAPON', 'PORTWEAPON') NOT NULL;
