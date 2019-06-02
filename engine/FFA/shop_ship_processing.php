<?php
$shipID = $var['ship_id'];

$bannedShips = array(
	SHIP_TYPE_ASSAULT_CRAFT,
	SHIP_TYPE_DARK_MIRAGE,
	SHIP_TYPE_DESTROYER,
	SHIP_TYPE_DEVASTATOR,
	SHIP_TYPE_EATER_OF_SOULS,
	SHIP_TYPE_FURY,
	SHIP_TYPE_MOTHER_SHIP,
	
	SHIP_TYPE_FEDERAL_ULTIMATUM,
	SHIP_TYPE_DEATH_CRUISER
);

if (in_array($shipID, $bannedShips)) {
	create_error('No top racial for you, ah na na na na!');
}

require(ENGINE . 'Default/shop_ship_processing.php');
