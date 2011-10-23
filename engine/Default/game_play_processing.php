<?php

// register game_id
SmrSession::$game_id = $var['game_id'];

// check if hof entry is there
$db->query('SELECT * FROM account_has_stats WHERE account_id = '.SmrSession::$account_id);
if (!$db->getNumRows())
	$db->query('INSERT INTO account_has_stats (account_id, HoF_name, games_joined) VALUES ('.$account->account_id.', ' . $db->escape_string($account->login, true) . ', 1)');

$player =& SmrPlayer::getPlayer(SmrSession::$account_id, $var['game_id']);
$player->setLastSectorID(0);
$player->updateLastCPLAction();
$player->update();

// get rid of old plotted course
$player->deletePlottedCourse();

// log
$account->log(2, 'Player entered game '.SmrSession::$game_id, $player->getSectorID());

$container = create_container('skeleton.php');
if ($player->isLandedOnPlanet())
    $container['body'] = 'planet_main.php';
else
    $container['body'] = 'current_sector.php';

$player	=& SmrPlayer::getPlayer(SmrSession::$account_id, SmrSession::$game_id);
$ship	=& $player->getShip();

// update turns on that player
$player->updateTurns();

// we cant move if we are dead
//check if we are in kill db...if we are we don't do anything
$db->query('SELECT * FROM kills WHERE dead_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID());
if (!$db->nextRecord()) {

	if ($ship->isDead() && ($var['body'] != 'trader_attack.php' && $var['url'] != 'trader_attack_processing.php' && $var['body'] != 'port_attack.php' && $var['url'] != 'port_attack_processing.php'&& $var['body'] != 'planet_attack.php' && $var['url'] != 'planet_attack_processing.php'))
	{
		$player->setSectorID($player->getHome());
		$player->setNewbieTurns(100);
		$player->update();
		$ship->get_pod();
		//$PHP_OUTPUT.=('.$db->escapeString($var[body], $var[url]');
		$container = create_container('skeleton.php','current_sector.php');
	}
} elseif (!isset($var['ahhh'])) {

	$db->query('SELECT * FROM kills WHERE dead_id = '.$player->getAccountID().' AND processed = \'TRUE\' AND game_id = '.$player->getGameID());
	if ($db->nextRecord() && $var['body'] != 'trader_attack.php') {
		$container = create_container('death_processing.php');
		$container['ahhh'] = 'Yes';
	}
}

if ($player->getNewbieTurns() <= 20 &&
	$player->getNewbieWarning() &&
	$var['url'] != 'newbie_warning_processing.php')
	$container = create_container('newbie_warning_processing.php');

$url = SmrSession::get_new_href($container);
SmrSession::update();
header('Location: '.$url);
exit;

?>