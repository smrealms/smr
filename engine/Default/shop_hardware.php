<?php

$template->assign('PageTopic','Hardware Shop');

if(!$player->getSector()->hasLocation($var['LocationID'])) {
	create_error('That location does not exist in this sector');
}

$location =& SmrLocation::getLocation($var['LocationID']);
if ($location->isHardwareSold()) {
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

	$hardwareSold = $location->getHardwareSold();
	foreach ($hardwareSold as $hardwareTypeID => $hardware) {
		$amount = $ship->getMaxHardware($hardwareTypeID) - $ship->getHardware($hardwareTypeID);

		$PHP_OUTPUT.=('<script type="text/javascript" language="JavaScript">
						function recalc_' . $hardwareTypeID . '_onkeyup() {
							window.document.forms['.$form.'].total.value = window.document.forms['.$form.'].amount.value * '.$hardware['Cost'].';
						}
					</script>');

		$form++;

		$container = create_container('shop_hardware_processing.php');
		transfer('LocationID');
		$container['hardware_id'] = $hardwareTypeID;

		$PHP_OUTPUT.=create_echo_form($container);
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td align="center">'.$hardware['Name'].'</td>');
		$PHP_OUTPUT.=('<td align="center"><input type="text" name="amount" value="'.$amount.'" size="5" onKeyUp="recalc_' . $hardwareTypeID . '_onkeyup()" id="InputFields" class="center"></td>');
		$PHP_OUTPUT.=('<td>*</td>');
		$PHP_OUTPUT.=('<td align="center">'.number_format($hardware['Cost']).'</td>');
		$PHP_OUTPUT.=('<td>=</td>');
		$PHP_OUTPUT.=('<td align="center"><input type="text" name="total" value="' . ($amount * $hardware['Cost']) . '" size="7" id="InputFields" class="center"></td>');
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