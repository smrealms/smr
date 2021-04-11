<?php declare(strict_types=1);

$var = Smr\Session::getInstance()->getCurrentVar();

Menu::planet_list($var['alliance_id'], 0);

require_once(get_file_loc('planet_list.inc.php'));
planet_list_common($var['alliance_id'], true);
