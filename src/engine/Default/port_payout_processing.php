<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$port = $player->getSectorPort();
$credits = match($var['PayoutType']) {
	'Raze' => $port->razePort($player),
	'Loot' => $port->lootPort($player),
};
$player->log(LOG_TYPE_TRADING, 'Player Triggers Payout: ' . $var['PayoutType']);
$port->update();
$container = Page::create('skeleton.php', 'current_sector.php');
$container['msg'] = 'You have taken <span class="creds">' . number_format($credits) . '</span> from the port.';
$container->go();
