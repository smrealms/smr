<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\HardwareType;
use Smr\Location;
use Smr\Page\AccountPage;
use Smr\ShipType;
use Smr\Template;
use Smr\WeaponType;

class EditLocations extends AccountPage {

	public function __construct(
		private readonly ?int $locationTypeID = null,
	) {}

	public function build(Account $account, Template $template): void {
		// For the purposes of editing, the game ID doesn't matter (yet)
		$gameID = 0;

		if ($this->locationTypeID !== null) {
			$template->pageRenderer = fn() => EditLocationsRenderer::renderEdit(
				ViewAllLocationsLink: new self()->href(),
				SaveChangesHREF: new EditLocationProcessor($this->locationTypeID)->href(),
				Location: Location::getLocation($gameID, $this->locationTypeID),
				ShipTypes: ShipType::getAll(),
				Weapons: WeaponType::getAllWeaponTypes(),
				AllHardware: HardwareType::getAll(),
			);
		} else {
			$template->pageRenderer = fn() => EditLocationsRenderer::renderSelect(
				Locations: Location::getAllLocations($gameID),
			);
		}
	}

}
