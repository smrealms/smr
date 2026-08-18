<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Galaxy;
use Smr\Game;
use Smr\Page\AccountPage;
use Smr\Race;
use Smr\Session;
use Smr\Template;

class CreateGalaxies extends AccountPage {

	public function __construct(
		private readonly int $gameID,
	) {}

	public function build(Account $account, Template $template): void {
		$session = Session::getInstance();
		$numGals = $session->getRequestVarInt('num_gals', 12);

		$game = Game::getGame($this->gameID);
		$template->pageTopic = 'Create Galaxies : ' . $game->getDisplayName();

		// Link for creating galaxies
		$container = new CreateGalaxiesProcessor($this->gameID, $numGals);
		$submit = [
			'value' => 'Create Galaxies',
			'href' => $container->href(),
		];

		// Create default list of galaxy names (starting with race names)
		$raceNames = Race::getPlayableNames();
		sort($raceNames);
		$defaultNames = [...$raceNames, ...self::GALAXY_NAMES];

		//Galaxy Creation area
		$galaxies = [];
		for ($i = 1; $i <= $numGals; ++$i) {
			$isRacial = $i <= count($raceNames);
			$galaxies[$i] = [
				'Name' => $defaultNames[$i - 1] ?? 'Unknown',
				'Width' => 10,
				'Height' => 10,
				'Type' => $isRacial ? Galaxy::TYPE_RACIAL : Galaxy::TYPE_NEUTRAL,
				'ForceMaxHours' => $isRacial ? 12 : 60,
			];
		}

		$template->pageRenderer = fn() => CreateGalaxiesRenderer::render(
			GameEnabled: $game->isEnabled(),
			UpdateNumGalsHREF: new self($this->gameID)->href(),
			Submit: $submit,
			GenerateHREF: new CreateGalaxiesAutoProcessor($this->gameID)->href(),
			UploadSmrFileHREF: new UploadSmrFileProcessor($this->gameID)->href(),
			NumGals: $numGals,
			Galaxies: $galaxies,
		);
	}

	public const array GALAXY_NAMES = [
		'Omar',
		'Salzik',
		'Manton',
		'Livstar',
		'Teryllia',
		'Doriath',
		'Anconus',
		'Valheru',
		'Sardine',
		'Clacher',
		'Tangeria',
		'Panumbra',
		'Schattenreich',
		'Dinrepkalap',
		'Besidkibilo',
		'Theraseth',
		'Ybirejan',
		'Qirekin',
		'Zelijar',
		'Dinrepyeter',
		'Eridanus',
		'Lacerta',
		'Pyxis',
		'Sagitta',
		'Scutum',
	];

}
