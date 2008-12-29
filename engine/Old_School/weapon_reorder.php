<?

$smarty->assign('PageTopic','WEAPON REORDER');

if (isset($var['up']) && is_numeric($var['up']))
{
	$ship->moveWeaponUp($var['up']);
}

if (isset($var['down']) && is_numeric($var['down']))
{
	$ship->moveWeaponDown($var['down']);
}

if ($ship->hasWeapons())
{

	$PHP_OUTPUT.=('<table cellspacing="0" cellpadding="3" border="0" class="standard">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th align="center">Weapon Name</th>');
	$PHP_OUTPUT.=('<th align="center">Shield Damage</th>');
	$PHP_OUTPUT.=('<th align="center">Armor Damage</th>');
	$PHP_OUTPUT.=('<th align="center">Power Level</th>');
	$PHP_OUTPUT.=('<th align="center">Accuracy</th>');
	$PHP_OUTPUT.=('<th align="center">Action</th>');
	$PHP_OUTPUT.=('</tr>');

	$shipWeapons =& $ship->getWeapons();
	foreach ($shipWeapons as $order_id => &$weapon)
	{
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td>'.$weapon->getName().'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$weapon->getShieldDamage().'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$weapon->getArmourDamage().'</td>');
		$PHP_OUTPUT.= '<td>';
		$PHP_OUTPUT.= $weapon->getPowerLevel();
		$PHP_OUTPUT.= '</td><td>';
		$PHP_OUTPUT.= $weapon->getBaseAccuracy();
		$PHP_OUTPUT.= '</td>';

		$PHP_OUTPUT.=('<td>');

		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'weapon_reorder.php';
		$container['up'] = $order_id;
		if($order_id > 1)
		{
			$PHP_OUTPUT.=create_link($container, '<img src="images/up.gif" alt="Switch up" title="Switch up">');
		}
		else
		{
			$PHP_OUTPUT.=create_link($container, '<img src="images/up_push.gif" alt="Push up" title="Push up">');
		}

		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'weapon_reorder.php';
		$container['down'] = $order_id;
		if($order_id < $ship->getNumWeapons())
		{
			$PHP_OUTPUT.=create_link($container, '<img src="images/down.gif" alt="Switch down" title="Switch down">');
		}
		else
		{
			$PHP_OUTPUT.=create_link($container, '<img src="images/down_push.gif" alt="Push down" title="Push down">');
		}

		$PHP_OUTPUT.=('</td>');

		$PHP_OUTPUT.=('</tr>');

	}

	$PHP_OUTPUT.=('</table>');

}
else
	$PHP_OUTPUT.=('You don\'t have any weapons!');

?>