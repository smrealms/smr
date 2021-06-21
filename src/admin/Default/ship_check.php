<?php declare(strict_types=1);

$template = Smr\Template::getInstance();

$template->assign('PageTopic', 'Ship Integrity Check');

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT * FROM ship_type_support_hardware, player, ship_has_hardware, hardware_type ' .
		   'WHERE ship_type_support_hardware.ship_type_id = player.ship_type_id AND ' .
				 'player.account_id = ship_has_hardware.account_id AND ' .
				 'player.game_id = ship_has_hardware.game_id AND ' .
				 'ship_type_support_hardware.hardware_type_id = ship_has_hardware.hardware_type_id AND ' .
				 'ship_has_hardware.hardware_type_id = hardware_type.hardware_type_id AND ' .
				 'amount > max_amount');

$excessHardware = [];
foreach ($dbResult->records() as $dbRecord) {
	$container = Page::create('ship_check_processing.php');
	$container['account_id'] = $dbRecord->getInt('account_id');
	$container['hardware'] = $dbRecord->getInt('hardware_type_id');
	$container['game_id'] = $dbRecord->getInt('game_id');
	$container['max_amount'] = $dbRecord->getInt('max_amount');

	$excessHardware[] = [
		'player' => htmlentities($dbRecord->getField('player_name')),
		'game_id' => $dbRecord->getInt('game_id'),
		'hardware' => $dbRecord->getField('hardware_name'),
		'amount' => $dbRecord->getInt('amount'),
		'max_amount' => $dbRecord->getInt('max_amount'),
		'fixHREF' => $container->href(),
	];
}
$template->assign('ExcessHardware', $excessHardware);
