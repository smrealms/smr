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

	public string $file = 'admin/location_edit.php';

	public function __construct(
		private readonly ?int $locationTypeID = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->assign('ViewAllLocationsLink', (new self())->href());

		// For the purposes of editing, the game ID doesn't matter (yet)
		$gameID = 0;

		if ($this->locationTypeID !== null) {
			$location = Location::getLocation($gameID, $this->locationTypeID);

			$container = new EditLocationProcessor($this->locationTypeID);
			$template->assign('SaveChangesHREF', $container->href());

			$template->assign('Location', $location);
			$template->assign('ShipTypes', ShipType::getAll());
			$template->assign('Weapons', WeaponType::getAllWeaponTypes());
			$template->assign('AllHardware', HardwareType::getAll());
		} else {
			$template->assign('Locations', Location::getAllLocations($gameID));
		}
	}

}
