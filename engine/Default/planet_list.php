<?php

require_once(get_file_loc('menu.inc'));
create_planet_list_menu($var['alliance_id'], 0);

require_once('planet_list.inc');
planet_list_common($var['alliance_id'], true);

?>
