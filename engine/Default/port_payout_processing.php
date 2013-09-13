<?php
$port =& $player->getSectorPort();
switch($var['PayoutType']) {
	case 'Raze':
		$port->razePort($player);
	break;
	case 'Loot':
		$port->lootPort($player);
	break;
	default:
		throw new Exception('Unknown payout type: ', $var['PayoutType']);
}
$account->log(LOG_TYPE_TRADING, 'Player Triggers Payout: ' . $var['PayoutType'], $player->getSectorID());
$port->update();
forward(create_container('skeleton.php', 'port_loot.php'));

?>