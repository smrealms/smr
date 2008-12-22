<?
$smarty->assign('PageTopic','WEAPON DEALER');
$db2 = new SMR_DB();
$db->query('SELECT * FROM location, location_sells_weapons, location_type, weapon_type ' .
					'WHERE location.sector_id = '.$player->getSectorID().' AND ' .
    					  'location.game_id = '.SmrSession::$game_id.' AND ' .
    					  'location.location_type_id = location_sells_weapons.location_type_id AND ' .
    					  'location_sells_weapons.location_type_id = location_type.location_type_id AND ' .
    					  'location_sells_weapons.weapon_type_id = weapon_type.weapon_type_id');

if ($db->nf() > 0 ) {

	$PHP_OUTPUT.=('<table cellspacing="0" cellpadding="1" border="0" class="standard">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th align="center">Name</th>');
	$PHP_OUTPUT.=('<th align="center">Shield Damage</th>');
	$PHP_OUTPUT.=('<th align="center">Armor Damage</th>');
	$PHP_OUTPUT.=('<th align="center">Accuracy</th>');
	$PHP_OUTPUT.=('<th align="center">Race</th>');
	$PHP_OUTPUT.=('<th align="center">Power Level</th>');
	$PHP_OUTPUT.=('<th align="center">Cost</th>');
	$PHP_OUTPUT.=('<th align="center">Action</th>');
	$PHP_OUTPUT.=('</tr>');

	while ($db->next_record()) {

		$weapon_name = $db->f('weapon_name');
		$weapon_type_id = $db->f('weapon_type_id');
		$shield_damage = $db->f('shield_damage');
		$armor_damage  = $db->f('armor_damage');
		$accuracy = $db->f('accuracy');
        $db2->query('SELECT * FROM weapon_type WHERE weapon_type_id = '.$weapon_type_id);
        $db2->next_record();
        $race_id = $db2->f('race_id');
		$power_level = $db->f('power_level');
		$cost = $db->f('cost');
		$buyer_restriction = $db->f('buyer_restriction');

        $db2->query('SELECT * FROM race WHERE race_id = '.$race_id);
        $db2->next_record();
        $weapon_race = $db2->f('race_name');

		$container = array();
		$container['url'] = 'shop_weapon_processing.php';
        if ($race_id !=1) {
			$weaponRelations = Globals::getRaceRelations(SmrSession::$game_id,$race_id);
        	if ($weaponRelations[$player->getRaceID()] + $player->getRelation($race_id) < 300)
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
		$PHP_OUTPUT.=('<td align="center">'.$armor_damage.'</td>');
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

	$smarty->assign('PageTopic','SELL WEAPONS');

	$PHP_OUTPUT.=('<table cellspacing="0" cellpadding="1" border="0" class="standard">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th align="center">Name</th>');
	$PHP_OUTPUT.=('<th align="center">Cash</th>');
	$PHP_OUTPUT.=('<th align="center">Action</th>');
	$PHP_OUTPUT.=('</tr>');

	$shipWeapons =& $ship->getWeapons();
	foreach ($shipWeapons as $order_id => &$weapon)
	{
			$cost = $weapon->getCost() / 2;

			$container = array();
			$container['url'] = 'shop_weapon_processing.php';
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