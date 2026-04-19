<?php declare(strict_types=1);

namespace Smr\Pages\Player\Mission;

use Smr\MissionState;
use Smr\Page\PlayerPageProcessor;
use Smr\Pages\Player\CurrentSector;
use Smr\Player;

class AbandonProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly MissionState $missionState,
	) {}

	public function build(Player $player): never {
		// Delete the mission so that it can be accepted again later.
		$this->missionState->delete();

		(new CurrentSector())->go();
	}

}
