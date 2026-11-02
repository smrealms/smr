<?php declare(strict_types=1);

namespace Smr\Irc;

final readonly class SupplyDemandEntry {

	public function __construct(
		public int $sector,
		public int $supplyDemand,
		public int $recordedAt,
		public string $channel,
	) {}

}
