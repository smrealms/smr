<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');

require_once(get_file_loc('SmrPlanet.class.inc'));

// create planet object
$planet =& SmrPlanet::getPlanet($player->getGameID(),$player->getSectorID());
$template->assign('PageTopic','Planet : '.$planet->getName().' [Sector #'.$player->getSectorID().']');
$template->assign('ThisPlanet',$planet);

include(get_file_loc('menue.inc'));
create_planet_menue();

$container = array();
$container['url'] = 'planet_defense_processing.php';
$container['type_id'] = 1;
$template->assign('TransferShieldsHref',SmrSession::get_new_href($container));

$container = array();
$container['url'] = 'planet_defense_processing.php';
$container['type_id'] = 4;

$template->assignByRef('TransferCDsHref',SmrSession::get_new_href($container));
?>