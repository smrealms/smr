<?php declare(strict_types=1);

class Menu extends AbstractMenu {

	// No bounties in Semi Wars games
	public static function headquarters() {
		global $var, $template;

		$links = [];
		$location = SmrLocation::getLocation($var['LocationID']);
		if ($location->isHQ()) {
			$links[] = ['government.php', 'Government'];
			$links[] = ['military_payment_claim.php', 'Claim Military Payment'];
		} elseif ($location->isUG()) {
			$links[] = ['underground.php', 'Underground'];
		} else {
			throw new Exception("Location is not HQ or UG: " . $location->getName());
		}

		$menuItems = [];
		$container = create_container('skeleton.php');
		$container['LocationID'] = $var['LocationID'];
		foreach ($links as $link) {
			$container['body'] = $link[0];
			$menuItems[] = [
				'Link' => SmrSession::getNewHREF($container),
				'Text' => $link[1],
			];
		}
		$template->assign('MenuItems', $menuItems);
	}

}
