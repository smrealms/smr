<?php

$template->assign('PageTopic','Create Universe - Adding Ports (5/10)');

$PHP_OUTPUT.=('<dl>');
$db->query('SELECT * FROM game WHERE game_id = ' . $db->escapeNumber($var['game_id']));
if ($db->nextRecord())
	$PHP_OUTPUT.=('<dt class="bold">Game<dt><dd>' . $db->getField('game_name') . '</dd>');
$PHP_OUTPUT.=('<dt class="bold">Task:<dt><dd>Adding ports</d>');
$PHP_OUTPUT.=('<dt class="bold">Description:<dt><dd style="width:50%;">');
$PHP_OUTPUT.=('Without ports there is no trading and it\'s called Space <i>Merchant</i> Realms! First you have to enter the total number of ports per galaxy. ');
$PHP_OUTPUT.=('In the next step you need to specify which type of port levels will be created.<br />PLEASE NOTE: The percentages of different port levels must add up to exactly 100! ');
$PHP_OUTPUT.=('You can see this in the <i>sum</i> column.</dd>');
$PHP_OUTPUT.=('</dl>');

// put galaxies into one array
$galaxies = array();
$db->query('SELECT DISTINCT galaxy.galaxy_id as id, galaxy_name as name
			FROM sector
			JOIN galaxy USING(galaxy_id)
			WHERE game_id = ' . $db->escapeNumber($var['game_id']) . '
			ORDER BY galaxy.galaxy_id');
while ($db->nextRecord())
	$galaxies[$db->getField('id')] = $db->getField('name');

$container = array();
$container['url']		= 'universe_create_ports_processing.php';
$container['game_id']	= $var['game_id'];
$PHP_OUTPUT.=create_echo_form($container);

$PHP_OUTPUT.=('<p>&nbsp;</p>');
$PHP_OUTPUT.=('<p><table cellpadding="5" border="0">');
$PHP_OUTPUT.=('<tr><td></td>');
foreach ($galaxies as $galaxy_id => $galaxy_name)
	$PHP_OUTPUT.=('<th>'.$galaxy_name.'</th>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<script type="text/javascript" language="JavaScript">'.EOL);
$PHP_OUTPUT.=('function sum_onkeyup(gal) {'.EOL);
$PHP_OUTPUT.=('window.document.FORM.elements[gal + (10 * ' . count($galaxies) . ')].value = ');
$PHP_OUTPUT.=('parseInt(window.document.FORM.elements[\$gal + (1 * ' . count($galaxies) . ')].value)+');
$PHP_OUTPUT.=('parseInt(window.document.FORM.elements[\$gal + (2 * ' . count($galaxies) . ')].value)+');
$PHP_OUTPUT.=('parseInt(window.document.FORM.elements[\$gal + (3 * ' . count($galaxies) . ')].value)+');
$PHP_OUTPUT.=('parseInt(window.document.FORM.elements[\$gal + (4 * ' . count($galaxies) . ')].value)+');
$PHP_OUTPUT.=('parseInt(window.document.FORM.elements[\$gal + (5 * ' . count($galaxies) . ')].value)+');
$PHP_OUTPUT.=('parseInt(window.document.FORM.elements[\$gal + (6 * ' . count($galaxies) . ')].value)+');
$PHP_OUTPUT.=('parseInt(window.document.FORM.elements[\$gal + (7 * ' . count($galaxies) . ')].value)+');
$PHP_OUTPUT.=('parseInt(window.document.FORM.elements[\$gal + (8 * ' . count($galaxies) . ')].value)+');
$PHP_OUTPUT.=('parseInt(window.document.FORM.elements[\$gal + (9 * ' . count($galaxies) . ')].value);'.EOL);
$PHP_OUTPUT.=('}'.EOL);
$PHP_OUTPUT.=('</script>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="right"><b style="font-size:80%;"># of Ports</b></td>');
foreach ($galaxies as $galaxy_id => $galaxy_name)
	$PHP_OUTPUT.=('<td align="center"><input type="number" name="ports['.$galaxy_id.']" size="3" id="InputFields" value="0" onKeyUp="sum_onkeyup('.$count.')" class="center"></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr><td colspan="'. (sizeof($galaxies) + 1) . '"><hr noshade size="1"></td></tr>');
$PHP_OUTPUT.=('<tr><td colspan="'. (sizeof($galaxies) + 1) . '" align="center">The following numbers are percentages! For each gal it have to add up to 100%!</td></tr>');

for ($level = 1; $level < 10; $level++) {

	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="right"><b style="font-size:80%;">Level '.$level.'</b></td>');
	$count = 0;

	if ($level == 1)
		$value = 100;
	else
		$value = 0;

	foreach ($galaxies as $galaxy_id => $galaxy_name) {
		$count++;
		$PHP_OUTPUT.=('<td align="center"><input type="number" name="input['.$galaxy_id.']['.$level.']" size="3" id="InputFields" value="'.$value.'" onKeyUp="sum_onkeyup('.$count.')" class="center"></td>');
	}
	$PHP_OUTPUT.=('</tr>');

}

$PHP_OUTPUT.=('<tr><td colspan="'. (sizeof($galaxies) + 1) . '"><hr noshade size="1"></td></tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="right"><b style="font-size:80%;">Sum</b></td>');
foreach ($galaxies as $galaxy_id => $galaxy_name)
	$PHP_OUTPUT.=('<td align="center"><input type="number" name="sum_'.$galaxy_id.'" size="3" id="InputFields" value="0" class="center"></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('</table></p>');

$PHP_OUTPUT.=create_submit('Next >>');
$PHP_OUTPUT.=('&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Skip >>');
$PHP_OUTPUT.=('</form>');

?>