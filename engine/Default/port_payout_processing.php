<?php
$port =& $player->getSectorPort();
switch($var['PayoutType']) {
	case 'Raze':
		$credits = $port->razePort($player);
	break;
	case 'Loot':
		$credits = $port->lootPort($player);
	break;
	default:
		throw new Exception('Unknown payout type: ', $var['PayoutType']);
}
$account->log(LOG_TYPE_TRADING, 'Player Triggers Payout: ' . $var['PayoutType'], $player->getSectorID());
$port->update();
$container = create_container('skeleton.php', 'current_sector.php');
$container['msg'] = 'You have taken <span class="creds">' . $credits . '</span> from the port.';
forward($container);

?>