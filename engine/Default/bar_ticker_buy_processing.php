<?php declare(strict_types=1);

if ($account->getTotalSmrCredits() < CREDITS_PER_TICKER) {
	create_error('You don\'t have enough SMR Credits. Donate to SMR to gain SMR Credits!');
}
$type = $_REQUEST['type'];
if (empty($type)) {
	create_error('You have to choose the type of ticker to buy.');
}
switch ($type) {
	case 'NEWS':
	case 'SCOUT':
	case 'BLOCK':
	break;
	default:
		create_error('The ticker you chose does not exist.');
}
$expires = TIME;
$ticker = $player->getTicker($type);
if ($ticker !== false) {
	$expires = $ticker['Expires'];
}
$expires += 5 * 86400;
$db->query('REPLACE INTO player_has_ticker (game_id, account_id, type, expires) VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escapeString($type) . ', ' . $db->escapeNumber($expires) . ')');

//take credits
$account->decreaseTotalSmrCredits(CREDITS_PER_TICKER);

//offer another drink and such
$container = create_container('skeleton.php', 'bar_main.php');
transfer('LocationID');
$container['message'] = '<div class="center">Your system has been added.  Enjoy!</div><br />';
forward($container);
