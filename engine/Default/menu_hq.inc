<?php declare(strict_types=1);

// NOTE: These menu functions are separate because they are the only
// ones that differ in other game modes.

function create_hq_menu() {
	global $var;
	$menu_items = array();
	$container = create_container('skeleton.php');
	$container['LocationID'] = $var['LocationID'];

	$container['body'] = 'government.php';
	$menu_items[] = create_link($container, 'Government', 'nav');

	$container['body'] = 'military_payment_claim.php';
	$menu_items[] = create_link($container, 'Claim Military Payment', 'nav');

	$container['body'] = 'bounty_claim.php';
	$menu_items[] = create_link($container, 'Claim Bounty', 'nav');

	$container['body'] = 'bounty_place.php';
	$menu_items[] = create_link($container, 'Place a Bounty', 'nav');

	create_menu($menu_items);
}

function create_ug_menu() {
	global $var;
	$menu_items = array();
	$container = create_container('skeleton.php');
	$container['LocationID'] = $var['LocationID'];
	$container['body'] = 'underground.php';
	$menu_items[] = create_link($container, 'Underground', 'nav');
	$container['body'] = 'bounty_claim.php';
	$menu_items[] = create_link($container, 'Claim Bounty', 'nav');
	$container['body'] = 'bounty_place.php';
	$menu_items[] = create_link($container, 'Place a Bounty', 'nav');

	create_menu($menu_items);
}
