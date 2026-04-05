<?php declare(strict_types=1);

namespace Smr\Pages\Player\Mission;

use Smr\AbstractPlayer;
use Smr\Mission;
use Smr\Page\PlayerPageProcessor;
use Smr\Pages\Player\CurrentSector;

class DeclineProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly Mission $mission,
	) {}

	public function build(AbstractPlayer $player): never {
		$player->declineMission($this->mission);

		(new CurrentSector())->go();
	}

}
