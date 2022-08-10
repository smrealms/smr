<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use AbstractSmrPlayer;
use Smr\Page\PlayerPageProcessor;
use Smr\Pages\Player\CurrentSector;
use SmrLocation;

class GovernmentProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $locationID
	) {}

	public function build(AbstractSmrPlayer $player): never {
		// Player has selected to become a deputy/smuggler
		$location = SmrLocation::getLocation($player->getGameID(), $this->locationID);
		if ($location->isHQ()) {
			$player->setAlignment(150);
		} elseif ($location->isUG()) {
			$player->setAlignment(-150);
		}

		(new CurrentSector())->go();
	}

}
