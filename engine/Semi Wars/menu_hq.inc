<?php declare(strict_types=1);

// NOTE: only difference from Default menus is that Bounty pages
// have been removed.

function create_hq_menu() {
	global $var;
	$menu_items = array();
	$container = create_container('skeleton.php');
	$container['LocationID'] = $var['LocationID'];

	$container['body'] = 'government.php';
	$menu_items[] = create_link($container, 'Government', 'nav');

	$container['body'] = 'military_payment_claim.php';
	$menu_items[] = create_link($container, 'Claim Military Payment', 'nav');

	create_menu($menu_items);
}

function create_ug_menu() {
	global $var;
	$menu_items = array();
	$container = create_container('skeleton.php');
	$container['LocationID'] = $var['LocationID'];
	$container['body'] = 'underground.php';
	$menu_items[] = create_link($container, 'Underground', 'nav');

	create_menu($menu_items);
}
