<?php

$template->assign('PageTopic','Create Universe - Adding Special Locations (4/10)');

$PHP_OUTPUT.=('<dl>');
$db->query('SELECT * FROM game WHERE game_id = ' . $db->escapeNumber($var['game_id']));
if ($db->nextRecord())
	$PHP_OUTPUT.=('<dt class="bold">Game:<dt><dd>' . $db->getField('game_name') . '</dd>');
$PHP_OUTPUT.=('<dt class="bold">Task:<dt><dd>Adding special Location</d>');
$PHP_OUTPUT.=('<dt class="bold">Description:<dt><dd style="width:50%;">Here you can add special locations, like Race Headquarters, Underground HQ, Bars and Banks. ');
$PHP_OUTPUT.=('Each Headquarter should be only once in the game. The numbers provided are absolut numbers per galaxy.<br />');
$PHP_OUTPUT.=('PLEASE NOTE: Galaxies with a Racial Headquarter DON\'T need additional FED Space, it will generated automatically for these!</dd>');
$PHP_OUTPUT.=('</dl>');

// put galaxies into one array (id => name)
$galaxies = array();
$db->query('SELECT DISTINCT galaxy.galaxy_id as id, galaxy_name as name
			FROM sector, galaxy
			WHERE game_id = ' . $db->escapeNumber($var['game_id']) . ' AND
				  sector.galaxy_id = galaxy.galaxy_id
			ORDER BY galaxy.galaxy_id');
while ($db->nextRecord())
	$galaxies[$db->getField('id')] = $db->getField('name');

// put races into an array (id => name)
$db->query('SELECT * FROM race
			WHERE race_id != 1
			ORDER BY race_id');
while($db->nextRecord())
	$races[$db->getField('race_id')] = $db->getField('race_name');

$container = array();
$container['url']		= 'universe_create_location_processing.php';
$container['game_id']	= $var['game_id'];
$PHP_OUTPUT.=create_echo_form($container);

$PHP_OUTPUT.=('<p>&nbsp;</p>');
$PHP_OUTPUT.=('<p><table cellpadding="5" border="0">');
$PHP_OUTPUT.=('<tr><td></td>');
foreach ($galaxies as $galaxy_id => $galaxy_name)
	$PHP_OUTPUT.=('<th>'.$galaxy_name.'</th>');
$PHP_OUTPUT.=('</tr>');

// hq
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="right"><b style="font-size:80%;">Headquarter</b></td>');
foreach ($galaxies as $galaxy_id => $galaxy_name) {
	$PHP_OUTPUT.=('<td>');
	$PHP_OUTPUT.=('<select name="id['.GOVERNMENT.']['.$galaxy_id.']" size="1">');
	$PHP_OUTPUT.=('<option value="1">[None]</option>');

	foreach ($races as $race_id => $race_name) {
		$PHP_OUTPUT.=('<option value="'.$race_id.'"');
		if ($race_id - 1 == $galaxy_id)
			$PHP_OUTPUT.=(' selected');
		$PHP_OUTPUT.=('>'.$race_name.'</option>');
	}

	$PHP_OUTPUT.=('</select>');
	$PHP_OUTPUT.=('</td>');
}
$PHP_OUTPUT.=('</tr>');

// fed
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="right"><b style="font-size:80%;">Federal Space</b></td>');
foreach ($galaxies as $galaxy_id => $galaxy_name)
	$PHP_OUTPUT.=('<td align="center"><input type="checkbox" name="id['.FED.']['.$galaxy_id.']"></td>');
$PHP_OUTPUT.=('</tr>');

// ug
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="right"><b style="font-size:80%;">Underground HQ</b></td>');
foreach ($galaxies as $galaxy_id => $galaxy_name)
	$PHP_OUTPUT.=('<td align="center"><input type="checkbox" name="id['.UNDERGROUND.']['.$galaxy_id.']"></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr><td colspan="'. (sizeof($galaxies) + 1) . '"><hr noshade size="1"></td></tr>');

// banks
$db->query('SELECT * FROM location_type JOIN location_is_bank USING(location_type_id) ORDER BY location_name');
while ($db->nextRecord()) {
	$location_name		= $db->getField('location_name');
	$location_type_id	= $db->getField('location_type_id');

	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right"><b style="font-size:80%;">'.$location_name.'</b></td>');
	foreach ($galaxies as $galaxy_id => $galaxy_name) $PHP_OUTPUT.=('<td align="center"><input type="input" name="id['.$location_type_id.']['.$galaxy_id.']" size="3" id="InputFields" value="0" class="center"></td>');
	$PHP_OUTPUT.=('</tr>');
}

$PHP_OUTPUT.=('<tr><td colspan="'. (sizeof($galaxies) + 1) . '"><hr noshade size="1"></td></tr>');

// bars
$db->query('SELECT * FROM location_type JOIN location_is_bar USING(location_type_id) ORDER BY location_name');
while ($db->nextRecord()) {
	$location_name		= $db->getField('location_name');
	$location_type_id	= $db->getField('location_type_id');

	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right"><b style="font-size:80%;">'.$location_name.'</b></td>');
	foreach ($galaxies as $galaxy_id => $galaxy_name)
		$PHP_OUTPUT.=('<td align="center"><input type="input" name="id['.$location_type_id.']['.$galaxy_id.']" size="3" id="InputFields" value="0" class="center"></td>');
	$PHP_OUTPUT.=('</tr>');
}

$PHP_OUTPUT.=('</table></p>');

$PHP_OUTPUT.=create_submit('Next >>');
$PHP_OUTPUT.=('&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Skip >>');
$PHP_OUTPUT.=('</form>');

?>