<?php
if(!ENABLE_BETA) {
	create_error('Beta functions are disabled.');
}

$template->assign('PageTopic','Beta LITE');

$PHP_OUTPUT.=('<span style="bold red">This is the <i>LITE</i> version of the crib...imagine the power of the real one.</span><br /><br />');

// container for all links
$container = create_container('beta_func_processing.php', 'beta_functions_lite.php');

//first lets let them map all
$container['func'] = 'Map';
$PHP_OUTPUT.=create_link($container,'Map all');
$PHP_OUTPUT.=('<br /><br />');

//next let them get money
$container['func'] = 'Money';
$PHP_OUTPUT.=create_link($container,'Load up the $$!!');
$PHP_OUTPUT.=('<br /><br />');

//next time for ship
$container['func'] = 'Ship';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<select name="ship_id">');
$db->query('SELECT * FROM ship_type WHERE ship_type_Id != 68 AND ship_type_id != 999 ORDER BY ship_name');
while ($db->nextRecord()) {
	$PHP_OUTPUT.=('<option value="' . $db->getInt('ship_type_id') . '">' . $db->getField('ship_name') . '</option>');
}
$PHP_OUTPUT.=('</select>&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Change Ship');
$PHP_OUTPUT.=('</form>');

//Remove Weapons
$container['func'] = 'RemWeapon';
$PHP_OUTPUT.=create_link($container,'Remove Weapons');
$PHP_OUTPUT.=('<br /><br />');

//allow to get full hardware
$container['func'] = 'Uno';
$PHP_OUTPUT.=create_link($container, 'Get Full Hardware');
$PHP_OUTPUT.=('<br /><br />');
//set experience
$container['func'] = 'Exp';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<input type="number" name="exp" value="'.$player->getExperience().'">&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Set Exp to Amount');
$PHP_OUTPUT.=('</form>');

//Set alignment
$container['func'] = 'Align';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<input type="number" name="align" value="'.$player->getAlignment().'">&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Set Align to Amount');
$PHP_OUTPUT.=('</form>');
