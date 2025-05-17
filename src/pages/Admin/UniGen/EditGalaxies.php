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
		public readonly EditGalaxy $returnTo,
	) {}

	public function build(Account $account, Template $template): void {
		$game = Game::getGame($this->gameID);
		$template->assign('PageTopic', 'Edit Galaxies : ' . $game->getDisplayName());
		$template->assign('GameEnabled', $game->isEnabled());

		$container = new EditGalaxiesProcessor($this->gameID, $this->returnTo);
		$submit = [
			'value' => 'Edit Galaxies',
			'href' => $container->href(),
		];
		$template->assign('Submit', $submit);

		$galaxies = [];
		foreach ($game->getGalaxies() as $galaxyId => $galaxy) {
			$container = new EditGalaxiesDelProcessor(
				gameId: $this->gameID,
				deleteGalaxyId: $galaxyId,
				returnTo: $this,
			);
			$galaxies[$galaxyId] = [
				'Name' => $galaxy->getDisplayName(),
				'Width' => $galaxy->getWidth(),
				'Height' => $galaxy->getHeight(),
				'Type' => $galaxy->getGalaxyType(),
				'ForceMaxHours' => $galaxy->getMaxForceTime() / 3600,
				'DelHREF' => $container->href(),
			];
		}
		$template->assign('Galaxies', $galaxies);

		$template->assign('BackHREF', $this->returnTo->href());

		$container = new EditGalaxiesAddProcessor($this->gameID, $this);
		$template->assign('AddHREF', $container->href());
		$template->assign('MaxAddId', $game->getNumberOfGalaxies() + 1);
	}

}
