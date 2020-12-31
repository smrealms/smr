<?php declare(strict_types=1);

$template->assign('PageTopic', 'Ship Integrity Check');

$db->query('SELECT * FROM ship_type_support_hardware, player, ship_has_hardware, hardware_type ' .
		   'WHERE ship_type_support_hardware.ship_type_id = player.ship_type_id AND ' .
				 'player.account_id = ship_has_hardware.account_id AND ' .
				 'player.game_id = ship_has_hardware.game_id AND ' .
				 'ship_type_support_hardware.hardware_type_id = ship_has_hardware.hardware_type_id AND ' .
				 'ship_has_hardware.hardware_type_id = hardware_type.hardware_type_id AND ' .
				 'amount > max_amount');

$excessHardware = [];
while ($db->nextRecord()) {
	$container = create_container('ship_check_processing.php');
	$container['account_id'] = $db->getInt('account_id');
	$container['hardware'] = $db->getInt('hardware_type_id');
	$container['game_id'] = $db->getInt('game_id');
	$container['max_amount'] = $db->getInt('max_amount');

	$excessHardware[] = [
		'player' => htmlentities($db->getField('player_name')),
		'game_id' => $db->getInt('game_id'),
		'hardware' => $db->getField('hardware_name'),
		'amount' => $db->getInt('amount'),
		'max_amount' => $db->getInt('max_amount'),
		'fixHREF' => SmrSession::getNewHREF($container),
	];
}
$template->assign('ExcessHardware', $excessHardware);
