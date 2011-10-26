<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');

// create planet object
$planet =& $player->getSectorPlanet();

$template->assign('PageTopic','Planet : '.$planet->getName().' [Sector #'.$player->getSectorID().']');

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

$db->query('SELECT * FROM good ORDER BY good_id');
while($db->nextRecord())
{

	$good_id	= $db->getField('good_id');
	$good_name	= $db->getField('good_name');

	if (!$ship->hasCargo($good_id) && !$planet->hasStockpile($good_id)) continue;

	$container = array();
	$container['url'] = 'planet_stockpile_processing.php';
	$container['good_id'] = $good_id;

	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td>'.$good_name.'</td>');
	$PHP_OUTPUT.=('<td align="center">' . $ship->getCargo($good_id) . '</td>');
	$PHP_OUTPUT.=('<td align="center">' . $planet->getStockpile($good_id) . '</td>');
	$PHP_OUTPUT.=('<td align="center"><input type="text" name="amount" value="' . $ship->getCargo($good_id) . '" id="InputFields" size="4" class="center"/></td>');
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