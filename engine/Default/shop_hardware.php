<?php

$template->assign('PageTopic','Hardware Shop');

$db->query('SELECT * FROM location
			JOIN location_type USING (location_type_id)
			JOIN location_sells_hardware USING (location_type_id)
			JOIN hardware_type USING (hardware_type_id)
			WHERE sector_id = '.$player->getSectorID().'
				AND game_id = '.$player->getGameID().'
				AND location_type_id = '.$var['LocationID']);

if ($db->getNumRows() > 0 )
{
	$PHP_OUTPUT.=('<table class="standard">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th align="center">Name</th>');
	$PHP_OUTPUT.=('<th align="center">Purchase Amount</th>');
	$PHP_OUTPUT.=('<th>&nbsp;</th>');
	$PHP_OUTPUT.=('<th align="center">Unit Cost</th>');
	$PHP_OUTPUT.=('<th>&nbsp;</th>');
	$PHP_OUTPUT.=('<th align="center" width="75">Totals</th>');
	$PHP_OUTPUT.=('<th align="center">Action</th>');
	$PHP_OUTPUT.=('</tr>');

	$form = 0;

	while ($db->nextRecord())
	{
		$hardware_name = $db->getField('hardware_name');
		$hardware_type_id = $db->getField('hardware_type_id');
		$cost = $db->getField('cost');

		$amount = $ship->getMaxHardware($hardware_type_id) - $ship->getHardware($hardware_type_id);

		$PHP_OUTPUT.=('<script type="text/javascript" language="JavaScript">'.EOL);
		$PHP_OUTPUT.=('function recalc_' . $hardware_type_id . '_onkeyup() {'.EOL);
		//$PHP_OUTPUT.=('window.document.form_$hardware_type_id.total.value = window.document.form_$hardware_type_id.amount.value * $cost;'.EOL);
		$PHP_OUTPUT.=('window.document.forms['.$form.'].total.value = window.document.forms['.$form.'].amount.value * '.$cost.';'.EOL);
		$PHP_OUTPUT.=('}'.EOL);
		$PHP_OUTPUT.=('</script>');

		$form++;

		$container = create_container('shop_hardware_processing.php');
		transfer('LocationID');
		$container['hardware_id'] = $hardware_type_id;
		$container['hardware_name'] = $hardware_name;
		$container['cost'] = $cost;

		$PHP_OUTPUT.=create_echo_form($container);
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td align="center">'.$hardware_name.'</td>');
		$PHP_OUTPUT.=('<td align="center"><input type="text" name="amount" value="'.$amount.'" size="5" onKeyUp="recalc_' . $hardware_type_id . '_onkeyup()" id="InputFields" class="center"></td>');
		$PHP_OUTPUT.=('<td>*</td>');
		$PHP_OUTPUT.=('<td align="center">'.$cost.'</td>');
		$PHP_OUTPUT.=('<td>=</td>');
		$PHP_OUTPUT.=('<td align="center"><input type="text" name="total" value="' . ($amount * $cost) . '" size="7" id="InputFields" class="center"></td>');
		$PHP_OUTPUT.=('<td align="center">');
		$PHP_OUTPUT.=create_submit('Buy');
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('</tr>');
		$PHP_OUTPUT.=('</form>');
	}

	$PHP_OUTPUT.=('</table>');

}
else $PHP_OUTPUT.=('I have nothing to sell to you. Get out of here!');

?>