<?php declare(strict_types=1);

class Menu extends AbstractMenu {

	// No bounties in Semi Wars games
	public static function headquarters(int $locationTypeID): void {
		$links = [];
		$location = SmrLocation::getLocation($locationTypeID);
		if ($location->isHQ()) {
			$links[] = ['government.php', 'Government'];
			$links[] = ['military_payment_claim.php', 'Claim Military Payment'];
		} elseif ($location->isUG()) {
			$links[] = ['underground.php', 'Underground'];
		} else {
			throw new Exception('Location is not HQ or UG: ' . $location->getName());
		}

		$menuItems = [];
		foreach ($links as $link) {
			$container = Page::create($link[0], ['LocationID' => $locationTypeID]);
			$menuItems[] = [
				'Link' => $container->href(),
				'Text' => $link[1],
			];
		}

		$template = Smr\Template::getInstance();
		$template->assign('MenuItems', $menuItems);
	}

}
