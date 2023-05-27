<?php declare(strict_types=1);

namespace Smr\Pages\Player\Mission;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPageProcessor;
use Smr\Pages\Player\CurrentSector;

class DeclineProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $missionID,
	) {}

	public function build(AbstractPlayer $player): never {
		$player->declineMission($this->missionID);

		(new CurrentSector())->go();
	}

}
