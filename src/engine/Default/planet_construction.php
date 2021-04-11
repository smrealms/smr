<?php declare(strict_types=1);

require('planet.inc.php');

$template = Smr\Template::getInstance();
$template->assign('Goods', Globals::getGoods());
