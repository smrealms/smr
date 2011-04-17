<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');
require_once(get_file_loc('SmrPlanet.class.inc'));
$planet =& SmrPlanet::getPlanet($player->getGameID(),$player->getSectorID());

$planet_player =& SmrPlayer::getPlayer($var['account_id'], SmrSession::$game_id);
$owner =& SmrPlayer::getPlayer($planet->owner_id, $player->getGameID());
if ($owner->getAllianceID() != $player->getAllianceID())
	create_error('You can not kick someone off a planet your alliance does not own!');
$message = 'You have been kicked from '.$planet->planet_name.' in #'.$player->getSectorID();
$player->sendMessage($planet_player->getAccountID(), 2, $message, false);

$planet_player->setLandedOnPlanet(false);
$planet_player->setKicked(true);
$planet_player->update();

forward(create_container('skeleton.php', 'planet_main.php'));

?>