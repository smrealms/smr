<?php declare(strict_types=1);

$template = Smr\Template::getInstance();

$template->assign('PageTopic', 'Configure Hardware');

if ($ship->hasCloak()) {
	$container = Page::create('configure_hardware_processing.php');
	if (!$ship->isCloaked()) {
		$container['action'] = 'Enable';
	} else {
		$container['action'] = 'Disable';
	}
	$template->assign('ToggleCloakHREF', $container->href());
}

if ($ship->hasIllusion()) {
	$container = Page::create('configure_hardware_processing.php');
	$container['action'] = 'Set Illusion';
	$template->assign('SetIllusionFormHREF', $container->href());

	$ships = array();
	$db = Smr\Database::getInstance();
	$db->query('SELECT ship_type_id,ship_name FROM ship_type ORDER BY ship_name');
	while ($db->nextRecord()) {
		$ships[$db->getInt('ship_type_id')] = $db->getField('ship_name');
	}
	$template->assign('IllusionShips', $ships);
	$container['action'] = 'Disable Illusion';
	$template->assign('DisableIllusionHref', $container->href());
}

if ($ship->hasJump()) {
	$container = Page::create('sector_jump_processing.php', '');
	$container['target_page'] = 'current_sector.php';
	$template->assign('JumpDriveFormLink', $container->href());
}
