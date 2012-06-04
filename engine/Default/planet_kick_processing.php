<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');
$planet =& $player->getSectorPlanet();

$planetPlayer =& SmrPlayer::getPlayer($var['account_id'], $player->getGameID());
$owner =& $planet->getOwner();
if ($owner->getAllianceID() != $player->getAllianceID())
	create_error('You can not kick someone off a planet your alliance does not own!');
$message = 'You have been kicked from '.$planet->getName().' in #'.$player->getSectorID();
$player->sendMessage($planetPlayer->getAccountID(), 2, $message, false);

$planetPlayer->setLandedOnPlanet(false);
$planetPlayer->setKicked(true);
$planetPlayer->update();

forward(create_container('skeleton.php', 'planet_main.php'));

?>