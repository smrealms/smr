<?php declare(strict_types=1);

$template->assign('PageTopic', 'Configure Hardware');

if ($ship->hasCloak()) {
	$container = create_container('configure_hardware_processing.php');
	if (!$ship->isCloaked()) {
		$container['action'] = 'Enable';
	} else {
		$container['action'] = 'Disable';
	}
	$template->assign('ToggleCloakHREF', SmrSession::getNewHREF($container));
}

if ($ship->hasIllusion()) {
	$container = create_container('configure_hardware_processing.php');
	$container['action'] = 'Set Illusion';
	$template->assign('SetIllusionFormHREF', SmrSession::getNewHREF($container));

	$ships = array();
	$db->query('SELECT ship_type_id,ship_name FROM ship_type ORDER BY ship_name');
	while ($db->nextRecord()) {
		$ships[$db->getField('ship_type_id')] = $db->getField('ship_name');
	}
	$template->assign('IllusionShips', $ships);
	$container['action'] = 'Disable Illusion';
	$template->assign('DisableIllusionHref', SmrSession::getNewHREF($container));
}

if ($ship->hasJump()) {
	$container = create_container('sector_jump_processing.php', '');
	$container['target_page'] = 'current_sector.php';
	$template->assign('JumpDriveFormLink', SmrSession::getNewHREF($container));
}
