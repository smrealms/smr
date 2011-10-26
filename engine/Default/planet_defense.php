<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');

// create planet object
$planet =& $player->getSectorPlanet();
$template->assign('PageTopic','Planet : '.$planet->getName().' [Sector #'.$player->getSectorID().']');
$template->assign('ThisPlanet',$planet);

require_once(get_file_loc('menu.inc'));
create_planet_menu();

$container = array();
$container['url'] = 'planet_defense_processing.php';
$container['type_id'] = 1;
$template->assign('TransferShieldsHref',SmrSession::get_new_href($container));

$container = array();
$container['url'] = 'planet_defense_processing.php';
$container['type_id'] = 4;

$template->assign('TransferCDsHref',SmrSession::get_new_href($container));
?>