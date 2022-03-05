<?php declare(strict_types=1);

$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$message = '<div class="center">';

if ($player->getCredits() < 10) {
	create_error('Come back when you get some money!');
}
$player->decreaseCredits(10);

$dbResult = $db->read('SELECT drink_id FROM player_has_drinks WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER by drink_id DESC LIMIT 1');
if ($dbResult->hasRecord()) {
	$curr_drink_id = $dbResult->record()->getInt('drink_id');
} else {
	$curr_drink_id = 0;
}

if (isset($var['action']) && $var['action'] != 'drink') {
	$drinkName = 'water';
	$message .= 'You ask the bartender for some water and you quickly down it.<br />';
	// have they been drinking recently?
	if ($curr_drink_id > 0) {
		$message .= 'You don\'t feel quite so intoxicated anymore.<br />';
		$db->write('DELETE FROM player_has_drinks WHERE ' . $player->getSQL() . ' LIMIT 1');
	}
	$player->increaseHOF(1, array('Bar', 'Drinks', 'Water'), HOF_PUBLIC);
} else {
	// choose which drink to serve
	if (rand(1, 20) == 1) {
		//only have a chance at special drinks if they are very lucky
		$drinkList = Smr\BarDrink::getAll();
	} else {
		$drinkList = Smr\BarDrink::getCommon();
	}
	$drinkName = $drinkList[array_rand($drinkList)];

	$curr_drink_id++;
	$db->insert('player_has_drinks', [
		'account_id' => $db->escapeNumber($player->getAccountID()),
		'game_id' => $db->escapeNumber($player->getGameID()),
		'drink_id' => $db->escapeNumber($curr_drink_id),
		'time' => $db->escapeNumber(Smr\Epoch::time()),
	]);

	if (!Smr\BarDrink::isSpecial($drinkName)) {
		$message .= ('You have bought a ' . $drinkName . ' for $10');
		$player->increaseHOF(1, array('Bar', 'Drinks', 'Alcoholic'), HOF_PUBLIC);
	} else {
		$message .= 'The bartender says, "I\'ve got something special for ya."<br />'
			. 'They turn around for a minute and whip up a ' . $drinkName . '.<br />'
			. 'You take a long, deep draught and feel like you have been drinking for hours.<br />'
			. Smr\BarDrink::getSpecialMessage($drinkName) . '<br />';
		$player->increaseHOF(1, array('Bar', 'Drinks', 'Special'), HOF_PUBLIC);
	}

	$dbResult = $db->read('SELECT count(*) FROM player_has_drinks WHERE ' . $player->getSQL());
	$num_drinks = $dbResult->record()->getInt('count(*)');
	//display woozy message
	$message .= '<br />You feel a little W' . str_repeat('oO', $num_drinks) . 'zy<br />';
}

$player->actionTaken('BuyDrink', array(
	'SectorID' => $player->getSectorID(),
	'Drink' => $drinkName
));

//see if the player blacksout or not
if (isset($num_drinks) && $num_drinks > 15) {
	$percent = rand(1, 25);
	$lostCredits = IRound($player->getCredits() * $percent / 100);

	$message .= '<span class="red">You decide you need to go to the restroom.  So you stand up and try to start walking but immediately collapse!<br />About 10 minutes later you wake up and find yourself missing ' . number_format($lostCredits) . ' credits</span><br />';

	$player->decreaseCredits($lostCredits);
	$player->increaseHOF(1, array('Bar', 'Robbed', 'Number Of Times'), HOF_PUBLIC);
	$player->increaseHOF($lostCredits, array('Bar', 'Robbed', 'Money Lost'), HOF_PUBLIC);

	$db->write('DELETE FROM player_has_drinks WHERE ' . $player->getSQL());

}
$player->increaseHOF(1, array('Bar', 'Drinks', 'Total'), HOF_PUBLIC);
$message .= '</div>';

$container = Page::create('skeleton.php', 'bar_main.php');
$container->addVar('LocationID');
$container['message'] = $message;
$container->go();
