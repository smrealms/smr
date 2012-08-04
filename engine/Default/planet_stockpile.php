<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');

// create planet object
$planet =& $player->getSectorPlanet();

$template->assign('PageTopic','Planet : '.$planet->getName().' [Sector #'.$planet->getSectorID().']');

require_once(get_file_loc('menu.inc'));
create_planet_menu();

$PHP_OUTPUT.=('<p>');
$PHP_OUTPUT.=('<table class="standard">');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>Good</th>');
$PHP_OUTPUT.=('<th>Ship</th>');
$PHP_OUTPUT.=('<th>Planet</th>');
$PHP_OUTPUT.=('<th>Amount</th>');
$PHP_OUTPUT.=('<th>Transfer to</th>');
$PHP_OUTPUT.=('</tr>');

$GOODS =& Globals::getGoods();
foreach($GOODS as $goodID => $good) {
	if (!$ship->hasCargo($goodID) && !$planet->hasStockpile($goodID)) continue;

	$container = create_container('planet_stockpile_processing.php');
	$container['good_id'] = $goodID;

	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td>'.$good['Name'].'</td>');
	$PHP_OUTPUT.=('<td align="center">' . $ship->getCargo($goodID) . '</td>');
	$PHP_OUTPUT.=('<td align="center">' . $planet->getStockpile($goodID) . '</td>');
	$PHP_OUTPUT.=('<td align="center"><input type="number" name="amount" value="' . $ship->getCargo($goodID) . '" id="InputFields" size="4" class="center"/></td>');
	$PHP_OUTPUT.=('<td>');
	$PHP_OUTPUT.=create_submit('Ship');
	$PHP_OUTPUT.=('&nbsp;');
	$PHP_OUTPUT.=create_submit('Planet');
	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('</tr>');
	$PHP_OUTPUT.=('</form>');
}

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</p>');

?>