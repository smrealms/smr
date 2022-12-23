<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Page\AccountPage;
use Smr\Session;
use Smr\Template;
use SmrAccount;
use SmrGalaxy;
use SmrGame;

class CreateGalaxies extends AccountPage {

	public string $file = 'admin/unigen/universe_create_galaxies.php';

	public function __construct(
		private readonly int $gameID
	) {}

	public function build(SmrAccount $account, Template $template): void {
		$session = Session::getInstance();
		$numGals = $session->getRequestVarInt('num_gals', 12);

		$game = SmrGame::getGame($this->gameID);
		$template->assign('PageTopic', 'Create Galaxies : ' . $game->getDisplayName());
		$template->assign('GameEnabled', $game->isEnabled());

		// Link for updating the number of galaxies
		$container = new self($this->gameID);
		$template->assign('UpdateNumGalsHREF', $container->href());

		// Link for creating galaxies
		$container = new CreateGalaxiesProcessor($this->gameID, $numGals);
		$submit = [
			'value' => 'Create Galaxies',
			'href' => $container->href(),
		];
		$template->assign('Submit', $submit);

		// Link for creating universe from SMR file
		$container = new UploadSmrFileProcessor($this->gameID);
		$template->assign('UploadSmrFileHREF', $container->href());

		//Galaxy Creation area
		$defaultNames = [0, 'Alskant', 'Creonti', 'Human', 'Ik\'Thorne', 'Nijarin', 'Salvene', 'Thevian', 'WQ Human', 'Omar', 'Salzik', 'Manton', 'Livstar', 'Teryllia', 'Doriath', 'Anconus', 'Valheru', 'Sardine', 'Clacher', 'Tangeria'];
		$template->assign('NumGals', $numGals);

		$galaxies = [];
		for ($i = 1; $i <= $numGals; ++$i) {
			$isRacial = $i <= 8;
			$galaxies[$i] = [
				'Name' => $defaultNames[$i] ?? 'Unknown',
				'Width' => 10,
				'Height' => 10,
				'Type' => $isRacial ? SmrGalaxy::TYPE_RACIAL : SmrGalaxy::TYPE_NEUTRAL,
				'ForceMaxHours' => $isRacial ? 12 : 60,
			];
		}
		$template->assign('Galaxies', $galaxies);
	}

}
