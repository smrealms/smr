<?php
if (!$player->isLandedOnPlanet()) {
	create_error('You are not on a planet!');
}

$planet =& $player->getSectorPlanet();
$template->assign('PageTopic','Planet : '.$planet->getName().' [Sector #'.$player->getSectorID().']');
require_once(get_file_loc('menu.inc'));
create_planet_menu($planet);

$template->assignByRef('ThisPlanet', $planet);
$template->assign('PlanetBuildings', Globals::getPlanetBuildings());


$template->assign('Goods', Globals::getGoods());
?>