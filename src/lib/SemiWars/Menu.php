<?php declare(strict_types=1);

class Menu extends AbstractMenu {

	// No bounties in Semi Wars games
	public static function headquarters(int $locationTypeID, bool $addBountyPages = false): void {
		parent::headquarters($locationTypeID, $addBountyPages);
	}

}
