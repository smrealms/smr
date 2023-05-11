<?php declare(strict_types=1);

namespace Smr;

/**
 * Data class for course plotting destinations stored by the player.
 */
class StoredDestination {

	public function __construct(
		public readonly string $label,
		public readonly int $sectorID,
		public readonly int $offsetTop,
		public readonly int $offsetLeft,
	) {}

	/**
	 * Returns the name to display for this destination, e.g. "#42 - UG".
	 */
	public function getDisplayName(): string {
		$name = '#' . $this->sectorID;
		if ($this->label !== '') {
			$name .= ' - ' . $this->label;
		}
		return $name;
	}

}
