<?php declare(strict_types=1);

require_once(LIB . 'Default/planet.inc.php');
planet_common();

$template = Smr\Template::getInstance();
$template->assign('Goods', Globals::getGoods());
