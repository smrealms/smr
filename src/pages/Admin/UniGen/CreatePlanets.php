<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Galaxy;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\PlanetTypes\PlanetType;
use Smr\Request;
use Smr\Template;

class CreatePlanets extends AccountPage {

	use ReusableTrait;
	public function __construct(
		private readonly int $gameID,
		private readonly EditGalaxy $returnTo,
		private ?int $galaxyID = null,
	) {}

	public function build(Account $account, Template $template): void {
		$this->galaxyID ??= Request::getInt('gal_on');

		// Get a list of all available planet types
		$allowedTypes = [];
		foreach (array_keys(PlanetType::PLANET_TYPES) as $PlanetTypeID) {
			$allowedTypes[$PlanetTypeID] = PlanetType::getTypeInfo($PlanetTypeID)->name();
		}

		// Initialize all planet counts to zero
		$numberOfPlanets = [];
		foreach (array_keys($allowedTypes) as $ID) {
			$numberOfPlanets[$ID] = 0;
		}

		// Get the current number of each type of planet
		$galaxy = Galaxy::getGalaxy($this->gameID, $this->galaxyID);
		foreach ($galaxy->getSectors() as $galSector) {
			if ($galSector->hasPlanet()) {
				$numberOfPlanets[$galSector->getPlanet()->getTypeID()]++;
			}
		}

		$template->pageRenderer = fn() => CreatePlanetsRenderer::render(
			Galaxies: Galaxy::getGameGalaxies($this->gameID),
			JumpGalaxyHREF: new self($this->gameID, $this->returnTo)->href(),
			AllowedTypes: $allowedTypes,
			Galaxy: $galaxy,
			NumberOfPlanets: $numberOfPlanets,
			CreatePlanetsFormHREF: new CreatePlanetsProcessor(
				$this->gameID,
				$this->galaxyID,
				$this->returnTo,
			)->href(),
			CancelHREF: $this->returnTo->href(),
		);
	}

}
