<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use Smr\AbstractPlayer;
use Smr\Location;
use Smr\Page\PlayerPageProcessor;
use Smr\Pages\Player\CurrentSector;

class GovernmentProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $locationID,
	) {}

	public function build(AbstractPlayer $player): never {
		// Player has selected to become a deputy/smuggler
		$location = Location::getLocation($player->getGameID(), $this->locationID);
		if ($location->isHQ()) {
			$player->setAlignment(150);
		} elseif ($location->isUG()) {
			$player->setAlignment(-150);
		}

		(new CurrentSector())->go();
	}

}
