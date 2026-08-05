<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Galaxy;
use Smr\Page\AccountPageProcessor;
use Smr\PlanetTypes\PlanetType;
use Smr\Request;
use Smr\Sector;

class CreatePlanetsProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly int $galaxyID,
		private readonly EditGalaxy $returnTo,
	) {}

	public function build(Account $account): never {
		$galaxy = Galaxy::getGalaxy($this->gameID, $this->galaxyID);
		$galSectors = $galaxy->getSectors();
		foreach ($galSectors as $galSector) {
			if ($galSector->hasPlanet()) {
				$galSector->removePlanet();
			}
		}

		foreach (array_keys(PlanetType::PLANET_TYPES) as $planetTypeID) {
			$numberOfPlanets = Request::getInt('type' . $planetTypeID);
			for ($i = 1; $i <= $numberOfPlanets; $i++) {
				$galSector = findValidSector(
					$galSectors,
					fn(Sector $sector): bool => !$sector->hasPlanet(), // 1 per sector
				);
				$galSector->createPlanet($planetTypeID);
			}
		}

		$this->returnTo->message = '<span class="green">Success</span> : added planets.';
		$this->returnTo->go();
	}

}
