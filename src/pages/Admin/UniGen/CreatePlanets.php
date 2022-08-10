<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\PlanetTypes\PlanetType;
use Smr\Request;
use Smr\Template;
use SmrAccount;
use SmrGalaxy;

class CreatePlanets extends AccountPage {

	use ReusableTrait;

	public string $file = 'admin/unigen/universe_create_planets.php';

	public function __construct(
		private readonly int $gameID,
		private ?int $galaxyID = null
	) {}

	public function build(SmrAccount $account, Template $template): void {
		$this->galaxyID ??= Request::getInt('gal_on');
		$template->assign('Galaxies', SmrGalaxy::getGameGalaxies($this->gameID));

		$container = new self($this->gameID);
		$template->assign('JumpGalaxyHREF', $container->href());

		// Get a list of all available planet types
		$allowedTypes = [];
		foreach (array_keys(PlanetType::PLANET_TYPES) as $PlanetTypeID) {
			$allowedTypes[$PlanetTypeID] = PlanetType::getTypeInfo($PlanetTypeID)->name();
		}
		$template->assign('AllowedTypes', $allowedTypes);

		// Initialize all planet counts to zero
		$numberOfPlanets = [];
		foreach (array_keys($allowedTypes) as $ID) {
			$numberOfPlanets[$ID] = 0;
		}

		// Get the current number of each type of planet
		$galaxy = SmrGalaxy::getGalaxy($this->gameID, $this->galaxyID);
		foreach ($galaxy->getSectors() as $galSector) {
			if ($galSector->hasPlanet()) {
				$numberOfPlanets[$galSector->getPlanet()->getTypeID()]++;
			}
		}

		$template->assign('Galaxy', $galaxy);
		$template->assign('NumberOfPlanets', $numberOfPlanets);

		// Form to make planet changes
		$container = new SaveProcessor($this->gameID, $this->galaxyID);
		$template->assign('CreatePlanetsFormHREF', $container->href());

		// HREF to cancel and return to the previous page
		$container = new EditGalaxy($this->gameID, $this->galaxyID);
		$template->assign('CancelHREF', $container->href());
	}

}
