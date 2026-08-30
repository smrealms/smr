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
	public function __construct(
		private readonly int $gameID,
		private EditGalaxy $returnTo,
		private ?int $galaxyID = null,
	) {}

	public function build(Account $account, Template $template): void {
		$this->galaxyID ??= Request::getInt('gal_on');
		$this->returnTo->galaxyID = $this->galaxyID;

		$locations = Location::getAllLocations($this->gameID);

		// Initialize all location counts to zero
		$totalLocs = [];
		$locNames = [];
		foreach ($locations as $location) {
			$totalLocs[$location->getTypeID()] = 0;
			$locNames[$location->getTypeID()] = $location->getName();
		}

		$galaxy = Galaxy::getGalaxy($this->gameID, $this->galaxyID);

		// Determine the current amount of each location
		foreach ($galaxy->getSectors() as $galSector) {
			foreach ($galSector->getLocations() as $sectorLocation) {
				$totalLocs[$sectorLocation->getTypeID()]++;
			}
		}

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

		// Build summary rows for locations that can be configured directly.
		$locationSummary = [
			'Total' => array_sum($totalLocs),
			'Categories' => [],
		];
		$shownLocationIDs = [];
		foreach ($categories->locTypes as $category => $locIDs) {
			$summaryLocations = [];
			$categoryLocationCount = 0;
			foreach ($locIDs as $locID) {
				$shownLocationIDs[] = $locID;
				$count = $totalLocs[$locID];
				if ($count > 0) {
					$categoryLocationCount += $count;
					$summaryLocations[] = [
						'Name' => $locNames[$locID],
						'Count' => $count,
					];
				}
			}
			if (count($summaryLocations) > 0) {
				$locationSummary['Categories'][] = [
					'Name' => $category,
					'Count' => $categoryLocationCount,
					'Locations' => $summaryLocations,
				];
			}
		}

		// Append locations that are created automatically with another location.
		$linkedLocations = [];
		$linkedLocationCount = 0;
		foreach ($totalLocs as $locID => $count) {
			if ($count > 0 && !in_array($locID, $shownLocationIDs, true)) {
				$linkedLocationCount += $count;
				$linkedLocations[] = [
					'Name' => $locNames[$locID],
					'Count' => $count,
				];
			}
		}
		if (count($linkedLocations) > 0) {
			$locationSummary['Categories'][] = [
				'Name' => 'Automatically linked',
				'Count' => $linkedLocationCount,
				'Locations' => $linkedLocations,
			];
		}

		$template->pageRenderer = fn() => CreateLocationsRenderer::render(
			Galaxies: Galaxy::getGameGalaxies($this->gameID),
			JumpGalaxyHREF: new self($this->gameID, $this->returnTo)->href(),
			Galaxy: $galaxy,
			TotalLocs: $totalLocs,
			LocationSummary: $locationSummary,
			LocText: $locText,
			LocTypes: $categories->locTypes,
			CreateLocationsFormHREF: new CreateLocationsProcessor(
				$this->gameID,
				$this->galaxyID,
				$this->returnTo,
			)->href(),
			CancelHREF: $this->returnTo->href(),
		);
	}

}
