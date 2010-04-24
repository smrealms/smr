<?php

print_topic("CONFIGURE HARDWARE");

echo '<small>';

if (empty($ship->hardware[HARDWARE_CLOAK]) && empty($ship->hardware[HARDWARE_ILLUSION])) {

	echo 'You have no configurable hardware!';
	return;

}

if (!empty($ship->hardware[HARDWARE_CLOAK])) {

	$container = array();
	$container['url'] = 'configure_hardware_processing.php';
	$container['body'] = '';

	print_form(create_container('configure_hardware_processing.php', ''));

	echo '<b>Cloaking Device:</b>&nbsp;&nbsp;&nbsp;&nbsp;';

	if (!$ship->cloak_active()) {
		$container['action'] = 'Enable';
		echo print_button($container,'Enable');
	}
	else {
		$container['action'] = 'Disable';
		echo print_button($container,'Disable');
	}
	echo '<br><br>';
}

echo '</small>';

if (!empty($ship->hardware[HARDWARE_ILLUSION])) {

	if ($ship->get_illusion() > 0)
		$default_id = $ship->get_illusion();
	else
		$default_id = $ship->ship_type_id;

	$container = array();
	$container['url'] = 'configure_hardware_processing.php';
	$container['body'] = '';
	$container['action'] = 'Set Illusion';
	print_form($container);
	echo '<b>Illusion Generator:</b><br><br>';
	echo '<table cellspacing="0" class="nobord">';
	echo '<tr><td>Ship:</td><td><select name="ship_id" size="1" id="InputFields">';

	$db->query('SELECT ship_type_id,ship_name FROM ship_type ORDER BY ship_name');
	while ($db->next_record()) {

		$ship_type_id	= $db->f('ship_type_id');
		$ship_name		= $db->f('ship_name');
		echo '<option value="' . $db->f('ship_type_id') . '"';
		if ($ship_type_id == $default_id) echo ' selected';
		echo '>' . $ship_name . '</option>';
	}

	echo '</select></td></tr>';


	$attack = $ship->get_illusion_attack();
	if (empty($attack))
		$attack = 0;
	$defense = $ship->get_illusion_defense();
	if (empty($defense))
		$defense = 0;

	echo '<tr><td>Attack/Defense</td>';
	echo '<td><input type="text" id="InputFields" name="attack" value="';
	echo $attack;
	echo '" size="4" style="text-align:center;">&nbsp;/&nbsp;<input type="text" id="InputFields" name="defense" value="';
	echo $defense;
	echo '" size="4" style="text-align:center;"></td>';
	echo '</tr><tr><td>&nbsp;</td><td>';
	print_submit("Set Illusion");
	echo '&nbsp;&nbsp;&nbsp;&nbsp;';
	$container['action'] = 'Disable Illusion';
	echo print_button($container,'Disable Illusion');
	print '</td></tr></table></form>';
}

?>