<?php

include('planet.inc');

$template->assign('PlanetBuildings', Globals::getPlanetBuildings());
$template->assign('Goods', Globals::getGoods());
