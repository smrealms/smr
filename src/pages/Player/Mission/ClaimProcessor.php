<?php declare(strict_types=1);

namespace Smr\Pages\Player\Mission;

use Smr\Mission;
use Smr\Page\PlayerPageProcessor;
use Smr\Pages\Player\CurrentSector;
use Smr\Player;

class ClaimProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly Mission $mission,
	) {}

	public function build(Player $player): never {
		$rewardText = $this->mission->claimReward($player);

		(new CurrentSector(missionMessage: $rewardText))->go();
	}

}
