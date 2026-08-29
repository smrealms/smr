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
		$template->pageTopic = 'Edit Sector #' . $editSector->getSectorID() . ' (' . $editSector->getGalaxy()->getDisplayName() . ')';

		$planet = $editSector->getPlanetOrNull();
		$port = $editSector->getPortOrNull();

		$sectorLocationIDs = array_pad(
			array_keys($editSector->getLocations()),
			UNI_GEN_LOCATION_SLOTS,
			0,
		);

		if ($editSector->hasWarp()) {
			$warpSector = $editSector->getWarpSector();
			$warpSectorID = $warpSector->getSectorID();
			$warpGal = $warpSector->getGalaxy()->getDisplayName();
		} else {
			$warpSectorID = 0;
			$warpGal = 'No Warp';
		}

		$template->pageRenderer = fn() => EditSectorRenderer::render(
			EditSector: $editSector,
			LastSector: Game::getGame($this->gameID)->getLastSectorID(),
			EditHREF: new EditSectorProcessor($this->gameID, $this->sectorID, $this->returnTo())->href(),
			Planet: $planet,
			Port: $port,
			SectorLocationIDs: $sectorLocationIDs,
			WarpGal: $warpGal,
			WarpSectorID: $warpSectorID,
			CancelHREF: $this->returnTo->href(),
			Message: $this->message,
			ThisAccount: $account,
		);
	}

}
