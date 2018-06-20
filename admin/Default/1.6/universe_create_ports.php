<?php
require_once(get_file_loc('SmrGalaxy.class.inc'));

if (isset($var['gal_on'])) $gal_on = $var['gal_on'];
else $PHP_OUTPUT.= 'Gal_on not found!!';

$galaxy = SmrGalaxy::getGalaxy($var['game_id'],$var['gal_on']);
//get totals
$total = array();
$totalPorts = array();
$total['Ports'] = 0;
for ($i=1;$i<=9;$i++) {
	$totalPorts[$i] = 0;
}
foreach ($galaxy->getSectors() as $galSector) {
	if($galSector->hasPort()) {
		$totalPorts[$galSector->getPort()->getLevel()]++;
		$total['Ports']++;
	}
}

//universe_create_ports.php
//get totals
$container = $var;
$container['url'] = '1.6/universe_create_save_processing.php';
$container['body'] = '1.6/universe_create_sectors.php';

$PHP_OUTPUT.= create_echo_form($container);
$PHP_OUTPUT.= 'Working on Galaxy : ' . $galaxy->getName() . ' (' . $galaxy->getGalaxyID() . ')<br />';
$PHP_OUTPUT.= '<table width="100%"><tr><td class="center">';
$PHP_OUTPUT.= '<table class="standard"><tr><th>Port Level</th><th>Number of Ports</th></tr>';
for ($i=1;$i<=9;$i++) {
	$PHP_OUTPUT.= '<tr><td class="right">Level ' . $i . '</td><td>';
	$PHP_OUTPUT.= '<input type="number" value="';
	$PHP_OUTPUT.= $totalPorts[$i];
	$PHP_OUTPUT.= '" size="5" name="port' . $i . '" onFocus="startCalc();" onBlur="stopCalc();"></td></tr>';
}
$PHP_OUTPUT.= '<tr><th class="right">Total</th><td><input type="number" disabled="disabled" size="5" name="total" value="';
$PHP_OUTPUT.= $total['Ports'];
$PHP_OUTPUT.= '"></td></tr>';
$PHP_OUTPUT.= '</table>';
$PHP_OUTPUT.= '</td><td class="center">';
$PHP_OUTPUT.= '<table class="standard"><tr><th>Port Race</th><th>% Distribution</th></tr>';

foreach (Globals::getRaces() as $race) {
	$PHP_OUTPUT.= '<tr><td class="right">' . $race['Race Name'] . '</td><td><input type="number" size="5" name="race' . $race['Race ID'] . '" value="0" onFocus="startRaceCalc();" onBlur="stopRaceCalc();"></td></tr>';
}
$PHP_OUTPUT.= '<tr><th class="right">Total</th><td><input type="number" disabled="disabled" size="5" name="racedist" value="0"></td></tr>';
$PHP_OUTPUT.= '<tr><td class="center" colspan="2">';
$PHP_OUTPUT.= '<div class="buttonA"><a class="buttonA" onClick="setEven();">&nbsp;Set All Equal&nbsp;</a></div></td></tr>';
$PHP_OUTPUT.= '</table>';
$PHP_OUTPUT.= '</td>';
$PHP_OUTPUT.= '</tr>';

$PHP_OUTPUT.= '<tr><td colspan="3" class="center"><input type="submit" name="submit" value="Create Ports">';
$PHP_OUTPUT.= '<br /><br /><input type="submit" name="submit" value="Cancel"></td></tr></table>';

$PHP_OUTPUT.= '</form>';

$PHP_OUTPUT.= '<span class="small">Note: When you press "Create Ports" this will rearrange all current ports.<br />';
$PHP_OUTPUT.= 'To add new ports without rearranging everything use the edit sector feature.</span>';
