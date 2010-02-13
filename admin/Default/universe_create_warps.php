<?php

$template->assign('PageTopic','Create Universe - Adding Warps (3/10)');

$PHP_OUTPUT.=('<dl>');
$db->query('SELECT * FROM game WHERE game_id = ' . $var['game_id']);
if ($db->nextRecord())
	$PHP_OUTPUT.=('<dt class="bold">Game<dt><dd>' . $db->getField('game_name') . '</dd>');
$PHP_OUTPUT.=('<dt class="bold">Task:<dt><dd>Adding warps</d>');
$PHP_OUTPUT.=('<dt class="bold">Description:<dt><dd style="width:50%;">');
$PHP_OUTPUT.=('Each galaxy must be connected to the outside world. Please add the warps between it to actually create the universe layout. There can only be one warp between two galaxies.</dd>');
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
$container['url']		= 'universe_create_warps_processing.php';
$container['game_id']	= $var['game_id'];
$PHP_OUTPUT.=create_echo_form($container);

$PHP_OUTPUT.=('<p>&nbsp;</p>');
$PHP_OUTPUT.=('<p><table cellpadding="5" border="0">');
$PHP_OUTPUT.=('<tr><td></td>');
foreach ($galaxies as $galaxy_id => $galaxy_name)
	$PHP_OUTPUT.=('<th>'.$galaxy_name.'</th>');
$PHP_OUTPUT.=('</tr>');

foreach ($galaxies as $galaxy_id_1 => $galaxy_name) {

	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right">'.$galaxy_name.'</td>');

	foreach ($galaxies as $galaxy_id_2 => $galaxy_name) {

		if ($galaxy_id_1 <= $galaxy_id_2)
			$PHP_OUTPUT.=('<td>&nbsp;</td>');
		else
			$PHP_OUTPUT.=('<td align="center"><input type="checkbox" name="warp['.$galaxy_id_1.']['.$galaxy_id_2.']"></td>');
	}
	$PHP_OUTPUT.=('</tr>');
}

$PHP_OUTPUT.=('</table></p>');

$PHP_OUTPUT.=create_submit('Next >>');
$PHP_OUTPUT.=('&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Skip >>');
$PHP_OUTPUT.=('</form>');

?>