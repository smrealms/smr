<?php

$template->assign('PageTopic','Create Universe - Adding Hardware (9/10)');

$PHP_OUTPUT.=('<dl>');
$db->query('SELECT * FROM game WHERE game_id = ' . $var['game_id']);
if ($db->nextRecord())
	$PHP_OUTPUT.=('<dt class="bold">Game<dt><dd>' . $db->getField('game_name') . '</dd>');
$PHP_OUTPUT.=('<dt class="bold">Task:<dt><dd>Adding hardware shops</d>');
$PHP_OUTPUT.=('<dt class="bold">Description:<dt><dd style="width:50%;">');
$PHP_OUTPUT.=('Hardware shops carry different things, like Scanner, Jump Drive or Forces. Each shop lists the type it sells. The values you provide here are absolute numbers per galaxies.</dd>');
$PHP_OUTPUT.=('</dl>');

// put galaxies into one array
$galaxies = array();
$db->query('SELECT DISTINCT galaxy.galaxy_id as id, galaxy_name as name
			FROM sector, galaxy
			WHERE game_id = ' . $var['game_id'] . ' AND
				  sector.galaxy_id = galaxy.galaxy_id
			ORDER BY galaxy.galaxy_id');
while ($db->nextRecord())
	$galaxies[$db->getField('id')] = $db->getField('name');

$container = array();
$container['url']		= 'universe_create_hardware_processing.php';
$container['game_id']	= $var['game_id'];
$PHP_OUTPUT.=create_echo_form($container);

$PHP_OUTPUT.=('<p>&nbsp;</p>');
$PHP_OUTPUT.=('<p><table cellpadding="5" border="0">');
$PHP_OUTPUT.=('<tr><td></td>');
foreach ($galaxies as $galaxy_id => $galaxy_name)
	$PHP_OUTPUT.=('<th>'.$galaxy_name.'</th>');
$PHP_OUTPUT.=('</tr>');

$db2 = new SmrMySqlDatabase();

// iterate over all hardware shops
$db->query('SELECT location_type_id, location_name FROM location_type ' .
					'WHERE location_type_id > 600 AND location_type_id < 700 ' .
					'ORDER BY location_name');
while ($db->nextRecord()) {

	$location_name		= $db->getField('location_name');
	$location_type_id	= $db->getField('location_type_id');

	// get all hardware stuff that is sold here
	$db2->query('SELECT * FROM location_type, location_sells_hardware, hardware_type ' .
						 'WHERE location_type.location_type_id = location_sells_hardware.location_type_id AND ' .
						 	   'location_sells_hardware.hardware_type_id = hardware_type.hardware_type_id AND ' .
						 	   'location_type.location_type_id = '.$location_type_id);
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right"><b style="font-size:80%;">'.$location_name.'</b><br />');
	while ($db2->nextRecord()) $PHP_OUTPUT.=('<span style="font-size:65%;">' . $db2->getField('hardware_name') . '</span><br />');
	$PHP_OUTPUT.=('</td>');
	foreach ($galaxies as $galaxy_id => $galaxy_name) $PHP_OUTPUT.=('<td align="center"><input type="input" name="id['.$location_type_id.']['.$galaxy_id.']" size="3" id="InputFields" value="0" class="center"></td>');
	$PHP_OUTPUT.=('</tr>');
}

$PHP_OUTPUT.=('</table></p>');

$PHP_OUTPUT.=create_submit('Next >>');
$PHP_OUTPUT.=('&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Skip >>');
$PHP_OUTPUT.=('</form>');

?>