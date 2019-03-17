<?php

$template->assign('PageTopic','Ship Integrity Check');

$PHP_OUTPUT.=('<br />');
$db->query('SELECT * FROM ship_type_support_hardware, player, ship_has_hardware, hardware_type ' .
		   'WHERE ship_type_support_hardware.ship_type_id = player.ship_type_id AND ' .
				 'player.account_id = ship_has_hardware.account_id AND ' .
				 'player.game_id = ship_has_hardware.game_id AND ' .
				 'ship_type_support_hardware.hardware_type_id = ship_has_hardware.hardware_type_id AND ' .
				 'ship_has_hardware.hardware_type_id = hardware_type.hardware_type_id AND ' .
				 'amount > max_amount');

if ($db->getNumRows()) {
	$PHP_OUTPUT.= create_table();
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th>Player</th>');
	$PHP_OUTPUT.=('<th>Type</th>');
	$PHP_OUTPUT.=('<th>Amount</th>');
	$PHP_OUTPUT.=('<th>Max Amount</th>');
	$PHP_OUTPUT.=('<th>Action</th>');
	$PHP_OUTPUT.=('</tr>');

	while ($db->nextRecord()) {

		$player_name = stripslashes($db->getField('player_name'));
		$type = $db->getField('hardware_name');
		$amount = $db->getField('amount');
		$max_amount = $db->getField('max_amount');

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td>'.$player_name.'</td>');
		$PHP_OUTPUT.=('<td>'.$type.'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$amount.'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$max_amount.'</td>');
		$PHP_OUTPUT.=('<td align="center">');

		$container = create_container('ship_check_processing.php');
		$container['account_id'] = $db->getField('account_id');
		$container['hardware'] = $db->getField('hardware_type_id');
		$container['game_id'] = $db->getField('game_id');
		$container['max_amount'] = $max_amount;
		$PHP_OUTPUT.=create_echo_form($container);
		$PHP_OUTPUT.=create_submit('Fix');
		$PHP_OUTPUT.=('</form>');

		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('</tr>');

	}

	$PHP_OUTPUT.=('</table>');

} else
	$PHP_OUTPUT.=('No overpowered ships!');
