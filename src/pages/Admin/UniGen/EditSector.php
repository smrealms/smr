<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Game;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Request;
use Smr\Sector;
use Smr\Template;

class EditSector extends AccountPage {

	use ReusableTrait;

	public string $file = 'admin/unigen/universe_create_sector_details.php';

	public function __construct(
		private readonly int $gameID,
		private readonly EditGalaxy $returnTo,
		private ?int $sectorID = null,
		public ?string $message = null,
	) {}

	private function returnTo(): self {
		// When returning to this page, we don't want any stale messages
		$clone = clone $this;
		$clone->message = null;
		return $clone;
	}

	public function build(Account $account, Template $template): void {
		$this->sectorID ??= Request::getInt('sector_edit');
		$editSector = Sector::getSector($this->gameID, $this->sectorID);
		$template->assign('PageTopic', 'Edit Sector #' . $editSector->getSectorID() . ' (' . $editSector->getGalaxy()->getDisplayName() . ')');
		$template->assign('EditSector', $editSector);

		$template->assign('LastSector', Game::getGame($this->gameID)->getLastSectorID());

		$container = new EditSectorProcessor($this->gameID, $this->sectorID, $this->returnTo());
		$template->assign('EditHREF', $container->href());

		$selectedPlanetType = 0;
		if ($editSector->hasPlanet()) {
			$selectedPlanetType = $editSector->getPlanet()->getTypeID();
			$template->assign('Planet', $editSector->getPlanet());
		}
		$template->assign('SelectedPlanetType', $selectedPlanetType);

		$selectedPortLevel = null;
		$selectedPortRaceID = null;
		if ($editSector->hasPort()) {
			$selectedPortLevel = $editSector->getPort()->getLevel();
			$selectedPortRaceID = $editSector->getPort()->getRaceID();
			$template->assign('Port', $editSector->getPort());
		}
		$template->assign('SelectedPortLevel', $selectedPortLevel);
		$template->assign('SelectedPortRaceID', $selectedPortRaceID);

		$sectorLocationIDs = array_pad(
			array_keys($editSector->getLocations()),
			UNI_GEN_LOCATION_SLOTS,
			0,
		);
		$template->assign('SectorLocationIDs', $sectorLocationIDs);

		if ($editSector->hasWarp()) {
			$warpSector = $editSector->getWarpSector();
			$warpSectorID = $warpSector->getSectorID();
			$warpGal = $warpSector->getGalaxy()->getDisplayName();
		} else {
			$warpSectorID = 0;
			$warpGal = 'No Warp';
		}
		$template->assign('WarpGal', $warpGal);
		$template->assign('WarpSectorID', $warpSectorID);

		$template->assign('CancelHREF', $this->returnTo->href());

		$template->assign('Message', $this->message);
	}

}
