<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Admin\UniGenLocationCategories;
use Smr\Galaxy;
use Smr\Location;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Request;
use Smr\Template;

class CreateLocations extends AccountPage {

	use ReusableTrait;

	public string $file = 'admin/unigen/universe_create_locations.php';

	public function __construct(
		private readonly int $gameID,
		private ?int $galaxyID = null
	) {}

	public function build(Account $account, Template $template): void {
		$this->galaxyID ??= Request::getInt('gal_on');
		$template->assign('Galaxies', Galaxy::getGameGalaxies($this->gameID));

		$container = new self($this->gameID);
		$template->assign('JumpGalaxyHREF', $container->href());

		$locations = Location::getAllLocations($this->gameID);

		// Initialize all location counts to zero
		$totalLocs = [];
		foreach ($locations as $location) {
			$totalLocs[$location->getTypeID()] = 0;
		}

		$galaxy = Galaxy::getGalaxy($this->gameID, $this->galaxyID);
		$template->assign('Galaxy', $galaxy);

		// Determine the current amount of each location
		foreach ($galaxy->getSectors() as $galSector) {
			foreach ($galSector->getLocations() as $sectorLocation) {
				$totalLocs[$sectorLocation->getTypeID()]++;
			}
		}
		$template->assign('TotalLocs', $totalLocs);

		// Remove any linked locations, as they will be added automatically
		// with any corresponding HQs.
		foreach ($locations as $location) {
			foreach ($location->getLinkedLocations() as $linkedLoc) {
				unset($locations[$linkedLoc->getTypeID()]);
			}
		}

		// Set any extra information to be displayed with each location
		$locText = [];
		$categories = new UniGenLocationCategories();
		foreach ($locations as $location) {
			$extra = '<span class="small"><br />';
			if ($location->isWeaponSold()) {
				$extra .= $categories->addLoc($location->getTypeID(), 'Weapons');
				foreach ($location->getWeaponsSold() as $weapon) {
					$extra .= $weapon->getName() . '&nbsp;&nbsp;&nbsp;(' . $weapon->getShieldDamage() . '/' . $weapon->getArmourDamage() . '/' . $weapon->getBaseAccuracy() . ')<br />';
				}
			}
			if ($location->isShipSold()) {
				$extra .= $categories->addLoc($location->getTypeID(), 'Ships');
				foreach ($location->getShipsSold() as $shipSold) {
					$extra .= $shipSold->getName() . '<br />';
				}
			}
			if ($location->isHardwareSold()) {
				$extra .= $categories->addLoc($location->getTypeID(), 'Hardware');
				foreach ($location->getHardwareSold() as $hardware) {
					$extra .= $hardware->name . '<br />';
				}
			}
			if ($location->isBar()) {
				$extra .= $categories->addLoc($location->getTypeID(), 'Bars');
			}
			if ($location->isBank()) {
				$extra .= $categories->addLoc($location->getTypeID(), 'Banks');
			}
			if ($location->isHQ() || $location->isUG() || $location->isFed()) {
				$extra .= $categories->addLoc($location->getTypeID(), 'Headquarters');
				foreach ($location->getLinkedLocations() as $linkedLoc) {
					$extra .= $linkedLoc->getName() . '<br />';
				}
			}
			if (!$categories->added($location->getTypeID())) {
				// Anything that doesn't fit the other categories
				$extra .= $categories->addLoc($location->getTypeID(), 'Miscellaneous');
			}
			$extra .= '</span>';

			$locText[$location->getTypeID()] = $location->getName() . $extra;
		}
		$template->assign('LocText', $locText);
		$template->assign('LocTypes', $categories->locTypes);

		// Form to make location changes
		$container = new SaveProcessor($this->gameID, $this->galaxyID);
		$template->assign('CreateLocationsFormHREF', $container->href());

		// HREF to cancel and return to the previous page
		$container = new EditGalaxy($this->gameID, $this->galaxyID);
		$template->assign('CancelHREF', $container->href());
	}

}
