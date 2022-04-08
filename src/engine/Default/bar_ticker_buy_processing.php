<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$account = $session->getAccount();
$player = $session->getPlayer();

if ($account->getTotalSmrCredits() < CREDITS_PER_TICKER) {
	create_error('You don\'t have enough SMR Credits. Donate to SMR to gain SMR Credits!');
}
$type = Smr\Request::get('type');
$expires = Smr\Epoch::time();
$ticker = $player->getTicker($type);
if ($ticker !== false) {
	$expires = $ticker['Expires'];
}
$expires += 5 * 86400;

$db = Smr\Database::getInstance();
$db->replace('player_has_ticker', [
	'game_id' => $db->escapeNumber($player->getGameID()),
	'account_id' => $db->escapeNumber($player->getAccountID()),
	'type' => $db->escapeString($type),
	'expires' => $db->escapeNumber($expires),
]);

//take credits
$account->decreaseTotalSmrCredits(CREDITS_PER_TICKER);

//offer another drink and such
$container = Page::create('skeleton.php', 'bar_main.php');
$container->addVar('LocationID');
$container['message'] = '<div class="center">Your system has been added.  Enjoy!</div><br />';
$container->go();
