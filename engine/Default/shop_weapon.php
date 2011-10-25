<?php
$template->assign('PageTopic','Weapon Dealer');
$db2 = new SmrMySqlDatabase();
$db->query('SELECT * FROM location, location_sells_weapons, location_type, weapon_type ' .
					'WHERE location.sector_id = '.$player->getSectorID().' AND ' .
    					  'location.game_id = '.$player->getGameID().' AND ' .
    					  'location.location_type_id = '.$var['LocationID'].' AND ' .
    					  'location.location_type_id = location_sells_weapons.location_type_id AND ' .
    					  'location_sells_weapons.location_type_id = location_type.location_type_id AND ' .
    					  'location_sells_weapons.weapon_type_id = weapon_type.weapon_type_id');

if ($db->getNumRows() > 0 ) {

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

	while ($db->nextRecord()) {

		$weapon_name = $db->getField('weapon_name');
		$weapon_type_id = $db->getField('weapon_type_id');
		$shield_damage = $db->getField('shield_damage');
		$armour_damage  = $db->getField('armour_damage');
		$accuracy = $db->getField('accuracy');
        $db2->query('SELECT * FROM weapon_type WHERE weapon_type_id = '.$weapon_type_id);
        $db2->nextRecord();
        $race_id = $db2->getField('race_id');
		$power_level = $db->getField('power_level');
		$cost = $db->getField('cost');
		$buyer_restriction = $db->getField('buyer_restriction');

        $db2->query('SELECT * FROM race WHERE race_id = '.$race_id);
        $db2->nextRecord();
        $weapon_race = $db2->getField('race_name');

		$container = create_container('shop_weapon_processing.php');
		transfer('LocationID');
        if ($race_id !=1) {
        	if ($player->getRelation($race_id) < 300)
        		$container['cant_buy'] = 'Yes';
        }
		$container['weapon_id'] = $weapon_type_id;
		$container['power_level'] = $power_level;
		$container['buyer_restriction'] = $buyer_restriction;
		$container['cost'] = $cost;
		$container['weapon_type_id'] = $weapon_type_id;
		$PHP_OUTPUT.=create_echo_form($container);

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td align="center">'.$weapon_name.'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$shield_damage.'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$armour_damage.'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$accuracy.'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$weapon_race.'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$power_level.'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$cost.'</td>');
		$PHP_OUTPUT.=('<td align="center">');
		$PHP_OUTPUT.=create_submit('Buy');
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('</tr>');
		$PHP_OUTPUT.=('</form>');

	}

	$PHP_OUTPUT.=('</table>');

}

if ($ship->hasWeapons())
{

	$template->assign('PageTopic','Sell Weapons');

	$PHP_OUTPUT.=('<table class="standard">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th align="center">Name</th>');
	$PHP_OUTPUT.=('<th align="center">Cash</th>');
	$PHP_OUTPUT.=('<th align="center">Action</th>');
	$PHP_OUTPUT.=('</tr>');

	$shipWeapons =& $ship->getWeapons();
	foreach ($shipWeapons as $order_id => &$weapon)
	{
			$cost = $weapon->getCost() / 2;

			$container = create_container('shop_weapon_processing.php');
			transfer('LocationID');
			$container['order_id'] = $order_id;
			$container['cash_back'] = $cost;
			$container['weapon_type_id'] = $weapon->getWeaponTypeID();
			$PHP_OUTPUT.=create_echo_form($container);

			$PHP_OUTPUT.=('<tr>');
			$PHP_OUTPUT.=('<td align="center">'.$weapon->getName().'</td>');
			$PHP_OUTPUT.=('<td align="center">'.$cost.'</td>');
			$PHP_OUTPUT.=('<td align="center">');
			$PHP_OUTPUT.=create_submit('Sell');
			$PHP_OUTPUT.=('</td>');
			$PHP_OUTPUT.=('</tr>');
			$PHP_OUTPUT.=('</form>');
	} unset($weapon);

	$PHP_OUTPUT.=('</table>');

}

?>