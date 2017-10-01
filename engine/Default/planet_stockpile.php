<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');

// create planet object
$planet =& $player->getSectorPlanet();

$template->assign('PageTopic','Planet : '.$planet->getName().' [Sector #'.$planet->getSectorID().']');

require_once(get_file_loc('menu.inc'));
create_planet_menu($planet);

$PHP_OUTPUT.=('<p>There are no goods present on your ship or the planet!</p>');
$present = false;
$table=('<p>');
$table.=('<table class="standard">');

$table.=('<tr>');
$table.=('<th></th>');
$table.=('<th>Good</th>');
$table.=('<th>Ship</th>');
$table.=('<th>Planet</th>');
$table.=('<th>Amount</th>');
$table.=('<th>Transfer to</th>');
$table.=('</tr>');

$GOODS =& Globals::getGoods();
foreach($GOODS as $goodID => $good) {
	if (!$ship->hasCargo($goodID) && !$planet->hasStockpile($goodID)) continue;
	if (!$present) {
		$present = true;
		$PHP_OUTPUT = "";
	}
		
	$container = create_container('planet_stockpile_processing.php');
	$container['good_id'] = $goodID;

	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td class="left"><img src="'.$good['ImageLink'].'" width="13" height="16" title="' . $good['Name'] . '" alt=""></td>');
	$PHP_OUTPUT.=('<td>'.$good['Name'].'</td>');
	$PHP_OUTPUT.=('<td align="center">' . $ship->getCargo($goodID) . '</td>');
	$PHP_OUTPUT.=('<td align="center">' . $planet->getStockpile($goodID) . '</td>');
	$default_amount = min($ship->getCargo($goodID),
	                      $planet->getRemainingStockpile($goodID));
	$PHP_OUTPUT.=('<td align="center"><input type="number" name="amount" value="' . $default_amount . '" id="InputFields" size="4" class="center"/></td>');
	$PHP_OUTPUT.=('<td>');
	$PHP_OUTPUT.=create_submit('Ship');
	$PHP_OUTPUT.=('&nbsp;');
	$PHP_OUTPUT.=create_submit('Planet');
	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('</tr>');
	$PHP_OUTPUT.=('</form>');
}

if ($present) {
	$PHP_OUTPUT = $table.$PHP_OUTPUT;
	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=('</p>');
}
?>
