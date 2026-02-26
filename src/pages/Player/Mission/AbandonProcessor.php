<?php declare(strict_types=1);

namespace Smr\Pages\Player\Mission;

use Smr\AbstractPlayer;
use Smr\MissionState;
use Smr\Page\PlayerPageProcessor;
use Smr\Pages\Player\CurrentSector;

class AbandonProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly MissionState $missionState,
	) {}

	public function build(AbstractPlayer $player): never {
		// Delete the mission so that it can be accepted again later.
		$this->missionState->delete();

		(new CurrentSector())->go();
	}

}
