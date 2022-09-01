<?php declare(strict_types=1);

use Smr\Request;

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();
$ship = $player->getShip();

if ($var['action'] == 'Enable') {
	if ($player->getTurns() < TURNS_TO_CLOAK) {
		create_error('You do not have enough turns to cloak.');
	}
	$player->takeTurns(TURNS_TO_CLOAK);
	$player->increaseHOF(TURNS_TO_CLOAK, ['Movement', 'Cloaking', 'Turns Used'], HOF_ALLIANCE);
	$player->increaseHOF(1, ['Movement', 'Cloaking', 'Times'], HOF_ALLIANCE);
	$ship->enableCloak();
} elseif ($var['action'] == 'Disable') {
	$ship->decloak();
} elseif ($var['action'] == 'Set Illusion') {
	$ship->setIllusion(Request::getInt('ship_type_id'), Request::getInt('attack'), Request::getInt('defense'));
} elseif ($var['action'] == 'Disable Illusion') {
	$ship->disableIllusion();
}

$container = Page::create('current_sector.php');
$container->go();
