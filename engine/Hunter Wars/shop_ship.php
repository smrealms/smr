<?php
$template->assign('PageTopic','Ship Dealer');

$db->query('SELECT ship_type_id FROM location
			JOIN location_sells_ships USING (location_type_id)
			WHERE sector_id = ' . $db->escapeNumber($player->getSectorID()) . '
				AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND location_type_id = '.$db->escapeNumber($var['LocationID']) . '
				AND ship_class_id != '.$db->escapeNumber(SmrShip::SHIP_CLASS_RAIDER) . '
				AND ship_type_id != '.$db->escapeNumber(SHIP_TYPE_PLANETARY_SUPER_FREIGHTER));

include('../Default/shop_ship.inc');
