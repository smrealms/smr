<?php declare(strict_types=1);
if (!$player->isLandedOnPlanet()) {
	create_error('You are not on a planet!');
}
$planet = $player->getSectorPlanet();

$planetPlayer = SmrPlayer::getPlayer($var['targetPlayerID'], $player->getGameID());
$owner = $planet->getOwner();
if ($owner->getAllianceID() != $player->getAllianceID()) {
	create_error('You can not kick someone off a planet your alliance does not own!');
}
$message = 'You have been kicked from ' . $planet->getDisplayName() . ' in ' . Globals::getSectorBBLink($player->getSectorID());
$player->sendMessage($planetPlayer->getPlayerID(), MSG_PLAYER, $message, false);

$planetPlayer->setLandedOnPlanet(false);
$planetPlayer->update();

forward(create_container('skeleton.php', 'planet_main.php'));
