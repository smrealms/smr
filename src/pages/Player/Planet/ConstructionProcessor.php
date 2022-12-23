<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use AbstractSmrPlayer;
use Smr\Page\PlayerPageProcessor;

class ConstructionProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly string $action,
		private readonly int $constructionID
	) {}

	public function build(AbstractSmrPlayer $player): never {
		if (!$player->isLandedOnPlanet()) {
			create_error('You are not on a planet!');
		}
		$planet = $player->getSectorPlanet();
		$action = $this->action;

		$constructionID = $this->constructionID;

		if ($action == 'Build') {
			// now start the construction
			$planet->startBuilding($player, $constructionID);
			$player->increaseHOF(1, ['Planet', 'Buildings', 'Started'], HOF_ALLIANCE);

			$player->log(LOG_TYPE_PLANETS, 'Player starts a ' . $planet->getStructureTypes($constructionID)->name() . ' on planet.');

		} elseif ($action == 'Cancel') {
			$planet->stopBuilding($constructionID);
			$player->increaseHOF(1, ['Planet', 'Buildings', 'Stopped'], HOF_ALLIANCE);
			$player->log(LOG_TYPE_PLANETS, 'Player cancels planet construction');
		}

		(new Construction())->go();
	}

}
