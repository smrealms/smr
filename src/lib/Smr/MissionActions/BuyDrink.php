<?php declare(strict_types=1);

namespace Smr\MissionActions;

use Smr\MissionAction;

readonly class BuyDrink extends MissionAction {

	public function __construct(
		public int $sectorID,
		public string $drink,
	) {}

}
