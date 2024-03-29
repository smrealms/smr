<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Game;
use Smr\Page\AccountPage;
use Smr\Template;

class EditGalaxies extends AccountPage {

	public string $file = 'admin/unigen/galaxies_edit.php';

	public function __construct(
		private readonly int $gameID,
		private readonly int $galaxyID,
	) {}

	public function build(Account $account, Template $template): void {
		$game = Game::getGame($this->gameID);
		$template->assign('PageTopic', 'Edit Galaxies : ' . $game->getDisplayName());
		$template->assign('GameEnabled', $game->isEnabled());

		$container = new EditGalaxiesProcessor($this->gameID, $this->galaxyID);
		$submit = [
			'value' => 'Edit Galaxies',
			'href' => $container->href(),
		];
		$template->assign('Submit', $submit);

		$galaxies = [];
		foreach ($game->getGalaxies() as $galaxy) {
			$galaxies[$galaxy->getGalaxyID()] = [
				'Name' => $galaxy->getDisplayName(),
				'Width' => $galaxy->getWidth(),
				'Height' => $galaxy->getHeight(),
				'Type' => $galaxy->getGalaxyType(),
				'ForceMaxHours' => $galaxy->getMaxForceTime() / 3600,
			];
		}
		$template->assign('Galaxies', $galaxies);

		$container = new EditGalaxy($this->gameID, $this->galaxyID);
		$template->assign('BackHREF', $container->href());
	}

}
