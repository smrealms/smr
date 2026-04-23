<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use Smr\Location;
use Smr\Page\PlayerPageProcessor;
use Smr\Pages\Player\CurrentSector;
use Smr\Player;

class GovernmentProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $locationID,
	) {}

	public function build(Player $player): never {
		// Player has selected to become a deputy/smuggler
		$location = Location::getLocation($player->getGameID(), $this->locationID);
		if ($location->isHQ()) {
			$player->setAlignment(ALIGNMENT_DEPUTY);
		} elseif ($location->isUG()) {
			$player->setAlignment(ALIGNMENT_SMUGGLER);
		}

		(new CurrentSector())->go();
	}

}
