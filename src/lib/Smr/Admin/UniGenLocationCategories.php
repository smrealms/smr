<?php declare(strict_types=1);

namespace Smr\Admin;

/**
 * Helper class for organizing Locations into categories.
 *
 * Though we expect a location to be only in one category, it is possible to
 * edit a location in the Admin Tools so that it is in two or more categories.
 * For simplicity here, it will only show up in the first category it matches,
 * but it will identify all other categories that it is in.
 * If multi-category locations becomes common, this code should be modified.
 */
class UniGenLocationCategories {

	/** @var array<string, array<int>> */
	public array $locTypes = [];
	/** @var array<int> */
	private array $locAdded = []; // list of locs added to a category

	public function addLoc(int $locID, string $category): string {
		if ($this->added($locID)) {
			return "<b>Also in $category</b><br />";
		}
		$this->locTypes[$category][] = $locID;
		$this->locAdded[] = $locID;
		return '';
	}

	public function added(int $locID): bool {
		return in_array($locID, $this->locAdded, true);
	}

}
