<?php declare(strict_types=1);

use Smr\PlanetList;

$var = Smr\Session::getInstance()->getCurrentVar();

Menu::planetList($var['alliance_id'], 0);

PlanetList::common($var['alliance_id'], true);
