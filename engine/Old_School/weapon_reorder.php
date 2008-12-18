<?

$smarty->assign('PageTopic','WEAPON REORDER');

$weapon_count = count($ship->weapon);

if (isset($var['up']) && is_numeric($var['up'])) {
	$weapon = $var['up'];
	$replacement = $weapon - 1;
	if($replacement < 1) {
		// Shift everything up by one and put the selected weapon at the bottom
		$temp = $ship->weapon[$weapon];
		for($i=1;$i<$weapon_count;++$i) {
			$ship->weapon[$i] = $ship->weapon[$i+1];
		}	
		$ship->weapon[$weapon_count] = $temp;
	}
	else {
		$temp =  $ship->weapon[$replacement];
		$ship->weapon[$replacement] = $ship->weapon[$weapon];
		$ship->weapon[$weapon]= $temp;
	}
	$ship->update_weapon();
}

if (isset($var['down']) && is_numeric($var['down'])) {
	$weapon = $var['down'];
	$replacement = $weapon + 1;
	if($replacement > $weapon_count) {
		// Shift everything down by one and put the selected weapon at the top
		$temp = $ship->weapon[$weapon_count];
		for($i=$weapon_count;$i>1;--$i) {
			$ship->weapon[$i] = $ship->weapon[$i-1];
		}	
		$ship->weapon[1] = $temp;
	}
	else {
		$temp =  $ship->weapon[$replacement];
		$ship->weapon[$replacement] = $ship->weapon[$weapon];
		$ship->weapon[$weapon]= $temp;
	}
	
	$ship->update_weapon();
}

if (count($ship->weapon) > 0) {

	$PHP_OUTPUT.=('<table cellspacing="0" cellpadding="3" border="0" class="standard">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th align="center">Weapon Name</th>');
	$PHP_OUTPUT.=('<th align="center">Shield Damage</th>');
	$PHP_OUTPUT.=('<th align="center">Armor Damage</th>');
	$PHP_OUTPUT.=('<th align="center">Power Level</th>');
	$PHP_OUTPUT.=('<th align="center">Accuracy</th>');
	$PHP_OUTPUT.=('<th align="center">Action</th>');
	$PHP_OUTPUT.=('</tr>');

	foreach ($ship->weapon as $order_id => $weapon_name) {

		$db->query('SELECT * FROM weapon_type WHERE weapon_name = '.$db->escapeString($weapon_name));
		$db->next_record();
		$shield_damage = $db->f('shield_damage');
		$armor_damage = $db->f('armor_damage');

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td>'.$weapon_name.'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$shield_damage.'</td>');
		$PHP_OUTPUT.=('<td align="center">'.$armor_damage.'</td>');
		$PHP_OUTPUT.= '<td>';
		$PHP_OUTPUT.= $db->f('power_level');
		$PHP_OUTPUT.= '</td><td>';
		$PHP_OUTPUT.= $db->f('accuracy');
		$PHP_OUTPUT.= '</td>';

		$PHP_OUTPUT.=('<td>');

		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'weapon_reorder.php';
		$container['up'] = $order_id;
		if($order_id > 1){
			$PHP_OUTPUT.=create_link($container, '<img src="images/up.gif" alt="Switch up" title="Switch up">');
		}
		else {
			$PHP_OUTPUT.=create_link($container, '<img src="images/up_push.gif" alt="Push up" title="Push up">');
		}

		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'weapon_reorder.php';
		$container['down'] = $order_id;
		if($order_id < $weapon_count){
			$PHP_OUTPUT.=create_link($container, '<img src="images/down.gif" alt="Switch down" title="Switch down">');
		}
		else {
			$PHP_OUTPUT.=create_link($container, '<img src="images/down_push.gif" alt="Push down" title="Push down">');
		}

		$PHP_OUTPUT.=('</td>');

		$PHP_OUTPUT.=('</tr>');

	}

	$PHP_OUTPUT.=('</table>');

} else
	$PHP_OUTPUT.=('You don\'t have any weapons!');

?>