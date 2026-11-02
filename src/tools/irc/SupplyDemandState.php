<?php declare(strict_types=1);

namespace Smr\Irc;

final class SupplyDemandState {

	/** @var list<SupplyDemandEntry> */
	private array $entries = [];

	public function replace(SupplyDemandEntry $new): void {
		$this->entries = array_values(array_filter(
			$this->entries,
			fn($entry) => $entry->channel === $new->channel && $entry->sector === $new->sector,
		));
		$this->entries[] = $new;
	}

	public function delete(string $channel, int $sector): bool {
		$entryCount = count($this->entries);
		$this->entries = array_values(array_filter(
			$this->entries,
			fn($entry) => $entry->channel !== $channel || $entry->sector !== $sector,
		));
		return count($this->entries) !== $entryCount;
	}

	/**
	 * @return list<SupplyDemandEntry>
	 */
	public function entriesForChannel(string $channel): array {
		return array_values(array_filter(
			$this->entries,
			fn(SupplyDemandEntry $entry): bool => $entry->channel === $channel,
		));
	}

}
