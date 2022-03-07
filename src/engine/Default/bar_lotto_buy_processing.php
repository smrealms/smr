<?php declare(strict_types=1);

$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

if ($player->getCredits() < 1000000) {
	create_error('There once was a man with less than $1,000,000...wait...thats you!');
}

$time = Smr\Epoch::time();
while (true) {
	//avoid double entries (since table is unique on game,account,time)
	$dbResult = $db->read('SELECT 1 FROM player_has_ticket WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND account_id = ' . $db->escapeNumber($player->getAccountID()) . ' AND time = ' . $db->escapeNumber($time));
	if (!$dbResult->hasRecord()) {
		break;
	}
	$time++;
}

$db->insert('player_has_ticket', [
	'game_id' => $db->escapeNumber($player->getGameID()),
	'account_id' => $db->escapeNumber($player->getAccountID()),
	'time' => $db->escapeNumber($time),
]);
$player->decreaseCredits(1000000);
$player->increaseHOF(1000000, ['Bar', 'Lotto', 'Money', 'Spent'], HOF_PUBLIC);
$player->increaseHOF(1, ['Bar', 'Lotto', 'Tickets Bought'], HOF_PUBLIC);
$dbResult = $db->read('SELECT count(*) as num FROM player_has_ticket WHERE ' . $player->getSQL() . ' AND time > 0 GROUP BY account_id');
$num = $dbResult->record()->getInt('num');
$message = ('<div class="center">Thanks for your purchase and good luck!  You currently');
$message .= (' own ' . $num . ' ' . pluralise('ticket', $num) . '!</div><br />');

$container = Page::create('skeleton.php', 'bar_main.php');
$container->addVar('LocationID');
$container['message'] = $message;
$container->go();
