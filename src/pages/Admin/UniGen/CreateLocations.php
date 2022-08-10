<?php declare(strict_types=1);

use Smr\Admin\UniGenLocationCategories;

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();

		$session->getRequestVarInt('gal_on');
		$template->assign('Galaxies', SmrGalaxy::getGameGalaxies($var['game_id']));

		$container = Page::create('admin/unigen/universe_create_locations.php');
		$container->addVar('game_id');
		$template->assign('JumpGalaxyHREF', $container->href());

		$locations = SmrLocation::getAllLocations($var['game_id']);

		// Initialize all location counts to zero
		$totalLocs = [];
		foreach ($locations as $location) {
			$totalLocs[$location->getTypeID()] = 0;
		}

		$galaxy = SmrGalaxy::getGalaxy($var['game_id'], $var['gal_on']);
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
					$extra .= $hardware['Name'] . '<br />';
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
		$container = Page::create('admin/unigen/universe_create_save_processing.php', $var);
		$container['forward_to'] = 'admin/unigen/universe_create_sectors.php';
		$template->assign('CreateLocationsFormHREF', $container->href());

		// HREF to cancel and return to the previous page
		$container = Page::create('admin/unigen/universe_create_sectors.php', $var);
		$template->assign('CancelHREF', $container->href());
