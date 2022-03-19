<?php declare(strict_types=1);

$var = Smr\Session::getInstance()->getCurrentVar();

Menu::planetList($var['alliance_id'], 0);

Smr\PlanetList::common($var['alliance_id'], true);
