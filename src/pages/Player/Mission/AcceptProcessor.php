<?php declare(strict_types=1);

namespace Smr\Pages\Player\Mission;

use AbstractSmrPlayer;
use Smr\Page\PlayerPageProcessor;
use Smr\Pages\Player\CurrentSector;

class AcceptProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $missionID
	) {}

	public function build(AbstractSmrPlayer $player): never {
		if (count($player->getMissions()) >= 3) {
			create_error('You can only have up to 3 missions at a time.');
		}

		$player->addMission($this->missionID);

		(new CurrentSector())->go();
	}

}
