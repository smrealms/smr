<?php declare(strict_types=1);

class Menu extends AbstractMenu {

	// No bounties in Semi Wars games
	public static function headquarters() {
		global $var;
		$menu_items = [];
		$container = create_container('skeleton.php');
		$container['LocationID'] = $var['LocationID'];

		$location = SmrLocation::getLocation($var['LocationID']);
		if ($location->isHQ()) {
			$container['body'] = 'government.php';
			$menu_items[] = create_link($container, 'Government', 'nav');
			$container['body'] = 'military_payment_claim.php';
			$menu_items[] = create_link($container, 'Claim Military Payment', 'nav');
		} elseif ($location->isUG()) {
			$container['body'] = 'underground.php';
			$menu_items[] = create_link($container, 'Underground', 'nav');
		} else {
			throw new Exception("Location is not HQ or UG: " . $location->getName());
		}
		create_menu($menu_items);
	}

}
