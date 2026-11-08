<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Game;
use Smr\Page\AccountPage;
use Smr\Template;

class EditGalaxies extends AccountPage {

	public function __construct(
		private readonly int $gameID,
		public readonly EditGalaxy $returnTo,
	) {}

	public function build(Account $account, Template $template): void {
		$game = Game::getGame($this->gameID);
		$template->pageTopic = 'Edit Galaxies : ' . $game->getDisplayName();

		$page = new EditGalaxiesProcessor($this->gameID, $this->returnTo);
		$submit = [
			'value' => 'Edit Galaxies',
			'href' => $page->href(),
		];

		$galaxies = [];
		foreach ($game->getGalaxies() as $galaxyId => $galaxy) {
			$page = new EditGalaxiesDelProcessor(
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
				'DelHREF' => $page->href(),
			];
		}

		$page = new EditGalaxiesAddProcessor($this->gameID, $this);

		$template->pageRenderer = fn() => EditGalaxiesRenderer::render(
			GameEnabled: $game->isEnabled(),
			Submit: $submit,
			Galaxies: $galaxies,
			BackHREF: $this->returnTo->href(),
			AddHREF: $page->href(),
			MaxAddId: $game->getNumberOfGalaxies() + 1,
		);
	}

}
