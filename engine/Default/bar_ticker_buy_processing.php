<?php declare(strict_types=1);

if ($account->getTotalSmrCredits() < CREDITS_PER_TICKER) {
	create_error('You don\'t have enough SMR Credits. Donate to SMR to gain SMR Credits!');
}
$type = Request::get('type');
$expires = TIME;
$ticker = $player->getTicker($type);
if ($ticker !== false) {
	$expires = $ticker['Expires'];
}
$expires += 5 * 86400;
$db->query('REPLACE INTO player_has_ticker (game_id, player_id, type, expires) VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($player->getPlayerID()) . ', ' . $db->escapeString($type) . ', ' . $db->escapeNumber($expires) . ')');

//take credits
$account->decreaseTotalSmrCredits(CREDITS_PER_TICKER);

//offer another drink and such
$container = create_container('skeleton.php', 'bar_main.php');
transfer('LocationID');
$container['message'] = '<div class="center">Your system has been added.  Enjoy!</div><br />';
forward($container);
