<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');

require_once(get_file_loc('SmrPlanet.class.inc'));

// create planet object
$planet =& SmrPlanet::getPlanet($player->getGameID(),$player->getSectorID());
$template->assign('PageTopic','Planet : '.$planet->planet_name.' [Sector #'.$player->getSectorID().']');

include(get_file_loc('menue.inc'));
create_planet_menue();

$PHP_OUTPUT.=('<p>');
$PHP_OUTPUT.=('<table class="standard">');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>Type</th>');
$PHP_OUTPUT.=('<th>Ship</th>');
$PHP_OUTPUT.=('<th>Planet</th>');
$PHP_OUTPUT.=('<th>Amount</th>');
$PHP_OUTPUT.=('<th>Transfer to</th>');
$PHP_OUTPUT.=('</tr>');

$container = array();
$container['url'] = 'planet_defense_processing.php';
$container['type_id'] = 1;

$PHP_OUTPUT.=create_echo_form($container);

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Shields</td>');
$PHP_OUTPUT.=('<td align="center">' . $ship->getShields() . '</td>');
$PHP_OUTPUT.=('<td align="center">'.$planet->shields.'</td>');
$PHP_OUTPUT.=('<td align="center"><input type="text" name="amount" value="0" id="InputFields" size="4" style="text-align:center;"></td>');
$PHP_OUTPUT.=('<td>');
$PHP_OUTPUT.=create_submit('Ship');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Planet');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('</form>');


$container = array();
$container['url'] = 'planet_defense_processing.php';
$container['type_id'] = 4;

$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Combat Drones</td>');
$PHP_OUTPUT.=('<td align="center">' . $ship->getCDs() . '</td>');
$PHP_OUTPUT.=('<td align="center">'.$planet->drones.'</td>');
$PHP_OUTPUT.=('<td align="center"><input type="text" name="amount" value="0" id="InputFields" size="4" style="text-align:center;"></td>');
$PHP_OUTPUT.=('<td>');
$PHP_OUTPUT.=create_submit('Ship');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Planet');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('</form>');

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</p>');

?>