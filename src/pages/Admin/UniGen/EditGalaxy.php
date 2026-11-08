<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Database;
use Smr\Exceptions\GalaxyNotFound;
use Smr\Galaxy;
use Smr\Game;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Request;
use Smr\Template;

/** @return ?array{name: string, href: string} */
function editGalaxyLink(bool $canEdit, int $gameID, int $newGalaxyId): ?array {
	try {
		$newGalaxy = Galaxy::getGalaxy($gameID, $newGalaxyId);
		return [
			'name' => $newGalaxy->getDisplayName(),
			'href' => new EditGalaxy($canEdit, $gameID, $newGalaxyId)->href(),
		];
	} catch (GalaxyNotFound) {
		return null;
	}
}

class EditGalaxy extends AccountPage {

	use ReusableTrait;
	public function __construct(
		private bool $canEdit, // edit or view-only
		private ?int $gameID = null,
		public ?int $galaxyID = null,
		public ?string $message = null,
		private ?int $focusSectorID = null,
	) {}

	private function returnTo(): self {
		// When returning to this page, we don't want any stale messages
		$clone = clone $this;
		$clone->message = null;
		return $clone;
	}

	public function build(Account $account, Template $template): void {
		$this->gameID ??= Request::getInt('game_id');
		$this->galaxyID ??= Request::getInt('gal_on', 1);
		if (Request::has('focus_sector_id')) {
			$this->focusSectorID = Request::getInt('focus_sector_id');
		}

		$returnTo = $this->returnTo(); // copy without message

		$galaxies = Galaxy::getGameGalaxies($this->gameID);
		if (count($galaxies) === 0) {
			// Game was created, but no galaxies exist, so go back to
			// the galaxy generation page
			new CreateGalaxies($this->gameID)->go();
		}

		$galaxy = Galaxy::getGalaxy($this->gameID, $this->galaxyID);

		// Efficiently construct the caches before proceeding
		$galaxy->getSectors();
		$galaxy->getPorts();
		$galaxy->getLocations();
		$galaxy->getPlanets();

		$connectivity = round($galaxy->getConnectivity());

		// Call this after all sectors have been cached in an efficient way.
		$mapSectors = $galaxy->getMapSectors($this->focusSectorID);

		// Get previous/next galaxies
		$prevGalaxy = editGalaxyLink($this->canEdit, $this->gameID, $this->galaxyID - 1);
		$nextGalaxy = editGalaxyLink($this->canEdit, $this->gameID, $this->galaxyID + 1);

		$game = Game::getGame($this->gameID);

		$lastSector = $game->getLastSectorID();

		$mapReady = null;
		$allEdit = null;
		$editLinks = null;
		if ($this->canEdit) {
			$editLinks = [
				'RedoConnections' => new RedoConnectionsProcessor($this->gameID, $this->galaxyID, $returnTo)->href(),
				'ModifySector' => new EditSector($this->gameID, $returnTo)->href(),
				'ModifyLocations' => new CreateLocations($this->gameID, $returnTo, $this->galaxyID)->href(),
				'ModifyPlanets' => new CreatePlanets($this->gameID, $returnTo, $this->galaxyID)->href(),
				'ModifyPorts' => new CreatePorts($this->gameID, $returnTo, $this->galaxyID)->href(),
				'ModifyWarps' => new CreateWarps($this->gameID, $this->galaxyID, $returnTo)->href(),
				'EditGameDetails' => new EditGame($this->gameID, $returnTo)->href(),
				'EditGalaxyDetails' => new EditGalaxies($this->gameID, $returnTo)->href(),
				'ResetGalaxy' => new ResetGalaxyProcessor($this->gameID, $this->galaxyID, $returnTo)->href(),
				'CreateStatus' => new EditGameCreateStatusProcessor($this->gameID, $returnTo)->href(),
			];

			$page = new ToggleLinkProcessor($this->gameID, $returnTo);
			$page->allowAjax = true;
			$editLinks['ToggleLink'] = $page->href();

			$page = new DragLocationProcessor($this->gameID, $returnTo);
			$page->allowAjax = true;
			$editLinks['DragLocation'] = $page->href();

			$page = new DragPlanetProcessor($this->gameID, $returnTo);
			$page->allowAjax = true;
			$editLinks['DragPlanet'] = $page->href();

			$page = new DragPortProcessor($this->gameID, $returnTo);
			$page->allowAjax = true;
			$editLinks['DragPort'] = $page->href();

			$page = new DragWarpProcessor($this->gameID, $returnTo);
			$page->allowAjax = true;
			$editLinks['DragWarp'] = $page->href();

			if (!$game->isEnabled()) {
				$db = Database::getInstance();
				$dbResult = $db->select(
					'game_create_status',
					['game_id' => $this->gameID],
					['ready_date', 'all_edit'],
				);
				$dbRecord = $dbResult->record();
				$mapReady = $dbRecord->getNullableString('ready_date') !== null;
				$allEdit = $dbRecord->getBoolean('all_edit');
			}
		}

		$template->pageRenderer = fn() => EditGalaxyRenderer::render(
			template: $template,
			ActualConnectivity: $connectivity,
			FocusSector: $this->focusSectorID,
			GameName: $game->getDisplayName(),
			Galaxy: $galaxy,
			Galaxies: $galaxies,
			MapSectors: $mapSectors,
			PrevGalaxy: $prevGalaxy,
			NextGalaxy: $nextGalaxy,
			LastSector: $lastSector,
			Message: $this->message,
			JumpGalaxyHREF: new self($this->canEdit, $this->gameID)->href(),
			RecenterHREF: new self($this->canEdit, $this->gameID, $this->galaxyID)->href(),
			BackButtonHREF: new CreateGame()->href(),
			SMRFileHREF: new SectorsFileDownloadProcessor($this->gameID)->href(),
			CheckMapHREF: new CheckMap($this->gameID, $returnTo)->href(),
			EditLinks: $editLinks,
			MapReady: $mapReady,
			AllEdit: $allEdit,
		);
	}

}
