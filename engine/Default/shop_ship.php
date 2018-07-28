<?php

$db->query('SELECT ship_type_id FROM location
			JOIN location_sells_ships USING (location_type_id)
			WHERE sector_id = ' . $db->escapeNumber($player->getSectorID()) . '
				AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND location_type_id = '.$db->escapeNumber($var['LocationID']));

include('shop_ship.inc');
