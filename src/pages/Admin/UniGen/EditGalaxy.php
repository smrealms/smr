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

class EditGalaxy extends AccountPage {

	use ReusableTrait;

	public string $file = 'admin/unigen/universe_create_sectors.php';

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
		$this->focusSectorID ??= Request::getInt('focus_sector_id', 0);

		$returnTo = $this->returnTo(); // copy without message

		$galaxies = Galaxy::getGameGalaxies($this->gameID);
		if (count($galaxies) === 0) {
			// Game was created, but no galaxies exist, so go back to
			// the galaxy generation page
			$container = new CreateGalaxies($this->gameID);
			$container->go();
		}

		$galaxy = Galaxy::getGalaxy($this->gameID, $this->galaxyID);

		// Efficiently construct the caches before proceeding
		$galaxy->getSectors();
		$galaxy->getPorts();
		$galaxy->getLocations();
		$galaxy->getPlanets();

		$connectivity = round($galaxy->getConnectivity());
		$template->assign('ActualConnectivity', $connectivity);

		// Call this after all sectors have been cached in an efficient way.
		if ($this->focusSectorID === 0) {
			$mapSectors = $galaxy->getMapSectors();
		} else {
			$mapSectors = $galaxy->getMapSectors($this->focusSectorID);
			$template->assign('FocusSector', $this->focusSectorID);
		}

		// Get previous/next galaxies
		$neighbours = [
			['PrevGalaxy', $this->galaxyID - 1],
			['NextGalaxy', $this->galaxyID + 1],
		];
		foreach ($neighbours as [$templateVar, $newGalaxyId]) {
			try {
				$newGalaxy = Galaxy::getGalaxy($this->gameID, $newGalaxyId);
				$newGalaxyInfo = [
					'name' => $newGalaxy->getDisplayName(),
					'href' => new self($this->canEdit, $this->gameID, $newGalaxyId)->href(),
				];
			} catch (GalaxyNotFound) {
				$newGalaxyInfo = null;
			}
			$template->assign($templateVar, $newGalaxyInfo);
		}

		$template->assign('GameName', Game::getGame($this->gameID)->getDisplayName());
		$template->assign('Galaxy', $galaxy);
		$template->assign('Galaxies', $galaxies);
		$template->assign('MapSectors', $mapSectors);

		$lastSector = end($galaxies)->getEndSector();
		$template->assign('LastSector', $lastSector);

		$template->assign('Message', $this->message);

		$container = new self($this->canEdit, $this->gameID);
		$template->assign('JumpGalaxyHREF', $container->href());

		$template->assign('RecenterHREF', new self($this->canEdit, $this->gameID)->href());

		$container = new CreateGame();
		$template->assign('BackButtonHREF', $container->href());

		$container = new SectorsFileDownloadProcessor($this->gameID);
		$template->assign('SMRFileHREF', $container->href());

		$container = new CheckMap($this->gameID, $returnTo);
		$template->assign('CheckMapHREF', $container->href());

		$template->assign('ThisPlayer', null);
		$template->assign('UniGen', $this->canEdit);

		if ($this->canEdit) {
			$container = new SaveProcessor($this->gameID, $this->galaxyID, $returnTo);
			$template->assign('SubmitChangesHREF', $container->href());

			$container = new ToggleLinkProcessor($this->gameID, $returnTo);
			$container->allowAjax = true;
			$template->assign('ToggleLinkHREF', $container->href());

			$container = new DragLocationProcessor($this->gameID, $returnTo);
			$container->allowAjax = true;
			$template->assign('DragLocationHREF', $container->href());

			$container = new DragPlanetProcessor($this->gameID, $returnTo);
			$container->allowAjax = true;
			$template->assign('DragPlanetHREF', $container->href());

			$container = new DragPortProcessor($this->gameID, $returnTo);
			$container->allowAjax = true;
			$template->assign('DragPortHREF', $container->href());

			$container = new DragWarpProcessor($this->gameID, $returnTo);
			$container->allowAjax = true;
			$template->assign('DragWarpHREF', $container->href());

			$container = new EditSector($this->gameID, $returnTo);
			$template->assign('ModifySectorHREF', $container->href());

			$container = new CreateLocations($this->gameID, $returnTo, $this->galaxyID);
			$template->assign('ModifyLocationsHREF', $container->href());

			$container = new CreatePlanets($this->gameID, $returnTo, $this->galaxyID);
			$template->assign('ModifyPlanetsHREF', $container->href());

			$container = new CreatePorts($this->gameID, $returnTo, $this->galaxyID);
			$template->assign('ModifyPortsHREF', $container->href());

			$container = new CreateWarps($this->gameID, $this->galaxyID, $returnTo);
			$template->assign('ModifyWarpsHREF', $container->href());

			$container = new EditGame($this->gameID, $returnTo);
			$template->assign('EditGameDetailsHREF', $container->href());

			$container = new EditGalaxies($this->gameID, $returnTo);
			$template->assign('EditGalaxyDetailsHREF', $container->href());

			$container = new ResetGalaxyProcessor($this->gameID, $this->galaxyID, $returnTo);
			$template->assign('ResetGalaxyHREF', $container->href());

			$container = new EditGameCreateStatusProcessor($this->gameID, $returnTo);
			$template->assign('CreateStatusHREF', $container->href());

			$db = Database::getInstance();
			$dbResult = $db->read('SELECT ready_date, all_edit FROM game_create_status WHERE game_id = :game_id', [
				'game_id' => $db->escapeNumber($this->gameID),
			]);
			$dbRecord = $dbResult->record();
			$template->assign('MapReady', $dbRecord->getNullableString('ready_date') !== null);
			$template->assign('AllEdit', $dbRecord->getBoolean('all_edit'));
		}

	}

}
