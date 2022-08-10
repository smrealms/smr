<?php declare(strict_types=1);

namespace Smr\Pages\Player\Mission;

use AbstractSmrPlayer;
use Smr\Page\PlayerPageProcessor;
use Smr\Pages\Player\CurrentSector;

class ClaimProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $missionID
	) {}

	public function build(AbstractSmrPlayer $player): never {
		$rewardText = $player->claimMissionReward($this->missionID);

		(new CurrentSector(missionMessage: $rewardText))->go();
	}

}
