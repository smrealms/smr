<?php declare(strict_types=1);

$template->assign('PageTopic', 'Ship Integrity Check');

$db->query('SELECT * FROM ship_type_support_hardware JOIN hardware_type USING (hardware_type_id) JOIN ship_has_hardware USING (hardware_type_id) JOIN player USING (player_id, game_id, ship_type_id) WHERE amount > max_amount');

$excessHardware = [];
while ($db->nextRecord()) {
	$container = create_container('ship_check_processing.php');
	$container['player_id'] = $db->getInt('player_id');
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
