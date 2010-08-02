<?php

$template->assign('PageTopic','Configure Hardware');

if (!$ship->hasCloak() && !$ship->hasIllusion())
{
	$PHP_OUTPUT.= 'You have no configurable hardware!';
	return;
}

$PHP_OUTPUT.= '<small>';

if ($ship->hasCloak()) 
{

	$container = create_container('configure_hardware_processing.php');

	$PHP_OUTPUT.= '<b>Cloaking Device:</b>&nbsp;&nbsp;&nbsp;&nbsp;';

	if (!$ship->isCloaked())
	{
		$container['action'] = 'Enable';
		$PHP_OUTPUT.= create_button($container,'Enable ('.TURNS_TO_CLOAK.')');
	}
	else
	{
		$container['action'] = 'Disable';
		$PHP_OUTPUT.= create_button($container,'Disable');
	}
	$PHP_OUTPUT.= '<br /><br />';
}

$PHP_OUTPUT.= '</small>';

if ($ship->hasIllusion())
{
	if ($ship->hasActiveIllusion())
		$default_id = $ship->getIllusionShipID();
	else
		$default_id = $ship->getShipTypeID();

	$container = array();
	$container['url'] = 'configure_hardware_processing.php';
	$container['body'] = '';
	$container['action'] = 'Set Illusion';
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.= '<b>Illusion Generator:</b><br /><br />';
	$PHP_OUTPUT.= '<table cellspacing="0" class="nobord">';
	$PHP_OUTPUT.= '<tr><td>Ship:</td><td><select name="ship_id" size="1" id="InputFields">';

	$db->query('SELECT ship_type_id,ship_name FROM ship_type ORDER BY ship_name');
	while ($db->nextRecord())
	{
		$ship_type_id	= $db->getField('ship_type_id');
		$PHP_OUTPUT.= '<option value="' . $ship_type_id . '"';
		if ($ship_type_id == $default_id) $PHP_OUTPUT.= ' selected';
		$PHP_OUTPUT.= '>' . $db->getField('ship_name') . '</option>';
	}

	$PHP_OUTPUT.= '</select></td></tr>';

	$attack = 0;
	$defense = 0;
	if ($ship->hasActiveIllusion())
	{
	$attack = $ship->getIllusionAttack();
	$defense = $ship->getIllusionDefense();
	}

	$PHP_OUTPUT.= '<tr><td>Attack/Defense</td>';
	$PHP_OUTPUT.= '<td><input type="text" id="InputFields" name="attack" value="';
	$PHP_OUTPUT.= $attack;
	$PHP_OUTPUT.= '" size="4" class="center">&nbsp;/&nbsp;<input type="text" id="InputFields" name="defense" value="';
	$PHP_OUTPUT.= $defense;
	$PHP_OUTPUT.= '" size="4" class="center"></td>';
	$PHP_OUTPUT.= '</tr><tr><td>&nbsp;</td><td>';
	$PHP_OUTPUT.=create_submit('Set Illusion');
	$PHP_OUTPUT.= '&nbsp;&nbsp;&nbsp;&nbsp;';
	$container['action'] = 'Disable Illusion';
	$PHP_OUTPUT.= create_button($container,'Disable Illusion');
	$PHP_OUTPUT.= '</td></tr></table></form>';
}

?>