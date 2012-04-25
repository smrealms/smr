<?php
$template->assign('PageTopic','Weapon Dealer');
$db->query('SELECT * FROM location
			JOIN location_type USING (location_type_id)
			JOIN location_sells_weapons USING (location_type_id)
			JOIN weapon_type USING (weapon_type_id)
			WHERE sector_id = ' . $db->escapeNumber($player->getSectorID()) . '
				AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND location_type_id = '.$db->escapeNumber($var['LocationID']));

$location =& SmrLocation::getLocation($var['LocationID']);
if ($location->isWeaponSold()) {
	$PHP_OUTPUT.=('<table class="standard">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th align="center">Name</th>');
	$PHP_OUTPUT.=('<th align="center">Shield Damage</th>');
	$PHP_OUTPUT.=('<th align="center">Armour Damage</th>');
	$PHP_OUTPUT.=('<th align="center">Accuracy</th>');
	$PHP_OUTPUT.=('<th align="center">Race</th>');
	$PHP_OUTPUT.=('<th align="center">Power Level</th>');
	$PHP_OUTPUT.=('<th align="center">Cost</th>');
	$PHP_OUTPUT.=('<th align="center">Action</th>');
	$PHP_OUTPUT.=('</tr>');

	$weaponsSold =& $location->getWeaponsSold();
	foreach($weaponsSold as &$weaponSold) {
		$container = create_container('shop_weapon_processing.php');
		transfer('LocationID');
		$container['weapon_type_id'] = $weaponSold->getWeaponTypeID();
		$PHP_OUTPUT.=create_echo_form($container);

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td align="center">'.$weaponSold->getName().'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$weaponSold->getShieldDamage().'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$weaponSold->getArmourDamage().'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$weaponSold->getBaseAccuracy().'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$weaponSold->getRaceName().'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$weaponSold->getPowerLevel().'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$weaponSold->getCost().'</td>');
		$PHP_OUTPUT.=('<td align="center">');
		$PHP_OUTPUT.=create_submit('Buy');
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('</tr>');
		$PHP_OUTPUT.=('</form>');
	}

	$PHP_OUTPUT.=('</table>');
}

if ($ship->hasWeapons()) {

	$template->assign('PageTopic','Sell Weapons');

	$PHP_OUTPUT.=('<table class="standard">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th align="center">Name</th>');
	$PHP_OUTPUT.=('<th align="center">Cash</th>');
	$PHP_OUTPUT.=('<th align="center">Action</th>');
	$PHP_OUTPUT.=('</tr>');

	$shipWeapons =& $ship->getWeapons();
	foreach ($shipWeapons as $order_id => &$weapon) {
		$container = create_container('shop_weapon_processing.php');
		transfer('LocationID');
		$container['order_id'] = $order_id;
		$container['weapon_type_id'] = $weapon->getWeaponTypeID();
		$PHP_OUTPUT.=create_echo_form($container);
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td align="center">'.$weapon->getName().'</td>');
		$PHP_OUTPUT.=('<td align="center">'.number_format(floor($weapon->getCost() * WEAPON_REFUND_PERCENT)).'</td>');
		$PHP_OUTPUT.=('<td align="center">');
		$PHP_OUTPUT.=create_submit('Sell');
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('</tr>');
		$PHP_OUTPUT.=('</form>');
	} unset($weapon);

	$PHP_OUTPUT.=('</table>');
}

?>