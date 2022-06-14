<?php declare(strict_types=1);

use Smr\PortPayoutType;

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$port = $player->getSectorPort();
if (!$port->isDestroyed()) {
	create_error('The port is no longer defenceless!');
}

/** @var PortPayoutType $payoutType */
$payoutType = $var['PayoutType'];

$credits = match ($payoutType) {
	PortPayoutType::Raze => $port->razePort($player),
	PortPayoutType::Loot => $port->lootPort($player),
};
$player->log(LOG_TYPE_TRADING, 'Player Triggers Payout: ' . $payoutType->name);
$port->update();
$container = Page::create('current_sector.php');
$container['msg'] = 'You have taken <span class="creds">' . number_format($credits) . '</span> from the port.';
$container->go();
