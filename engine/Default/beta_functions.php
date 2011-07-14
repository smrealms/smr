<?php
if(!ENABLE_BETA)
	create_error('Beta functions are disabled');

$template->assign('PageTopic','Beta things');

$PHP_OUTPUT.=('<span class="bold red">BIG WARNING! Be reasonable with he things you load to your ship! NEVER, i repeat NEVER (and it means NEVER) load more on a ship than suppossed to be on. NEVER give you more relation than 500. NEVER put you in a sector that doesn\'t exist! Don\'t say you haven\'t been warned! I WILL NOT fix your trader if you did!</span><br /><br />');

// container for all links
$container = create_container('beta_func_processing.php', 'beta_functions.php');

//first lets let them map all
$container['func'] = 'Map';
$PHP_OUTPUT.=create_link($container,'Map all');
$PHP_OUTPUT.=('<br />');

//next let them get money
$container['func'] = 'Money';
$PHP_OUTPUT.=create_link($container,'Load up the $$!!');
$PHP_OUTPUT.=('<br />');
$container['func'] = 'PageNewb';
$PHP_OUTPUT.=create_link($container,'Remove Page\'s newbie turns!!');
$PHP_OUTPUT.=('<br />');

//next time for ship
$container['func'] = 'Ship';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<select name="ship_id">');
$db->query('SELECT * FROM ship_type ORDER BY ship_name');
while ($db->nextRecord())
	$PHP_OUTPUT.=('<option value="' . $db->getField('ship_type_id') . '">' . $db->getField('ship_name') . '</option>');
$PHP_OUTPUT.=('</select>&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Change Ship');
$PHP_OUTPUT.=('</form>');
$PHP_OUTPUT.=('<br />');

//next weapons
$container['func'] = 'Weapon';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('Amount:&nbsp;&nbsp;<input type="text" name="amount" value="1"><br />');
$PHP_OUTPUT.=('<select name="weapon_id">');
$db->query('SELECT * FROM weapon_type ORDER BY weapon_name');
while ($db->nextRecord())
	$PHP_OUTPUT.=('<option value="' . $db->getField('weapon_type_id') . '">' . $db->getField('weapon_name') . '</option>');
$PHP_OUTPUT.=('</select>&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Add Weapon(s)');
$PHP_OUTPUT.=('</form>');

//Remove Weapons
$container['func'] = 'RemWeapon';
$PHP_OUTPUT.=create_link($container,'Remove Weapons');
$PHP_OUTPUT.=('<br />');

//allow to get full hardware
$container['func'] = 'Uno';
$PHP_OUTPUT.=create_link($container, 'Get Full Hardware');

//move whereever u want
$container['func'] = 'Warp';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<input type="text" name="sector_to" value="'.$player->getSectorID().'">&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Warp to Sector');
$PHP_OUTPUT.=('</form>');

//set turns
$container['func'] = 'Turns';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<input type="text" name="turns" value="'.$player->getTurns().'">&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Set Turns');
$PHP_OUTPUT.=('</form>');

//set experience
$container['func'] = 'Exp';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<input type="text" name="exp" value="'.$player->getExperience().'">&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Set Exp to Amount');
$PHP_OUTPUT.=('</form>');

//Set alignment
$container['func'] = 'Align';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<input type="text" name="align" value="'.$player->getAlignment().'">&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Set Align to Amount');
$PHP_OUTPUT.=('</form>');

$db->query('SELECT kills, experience_traded
			FROM account_has_stats
			WHERE account_id = '.SmrSession::$account_id);
if ($db->nextRecord()) {

	//Set kills
	$container['func'] = 'Kills';
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('<input type="text" name="kills" value="' . $db->getField('kills') . '">&nbsp;&nbsp;');
	$PHP_OUTPUT.=create_submit('Set Kills to Amount');
	$PHP_OUTPUT.=('</form>');

	//Set traded xp
	$container['func'] = 'Traded_XP';
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('<input type=text name="traded_xp" value="' . $db->getField('experience_traded') . '">&nbsp;&nbsp;');
	$PHP_OUTPUT.=create_submit('Set Traded XP to Amount');
	$PHP_OUTPUT.=('</form>');

}

$PHP_OUTPUT.=('<br />Note: This sets your hardware not adds it. Also, if u have more than 1 JD,scanner,etc they may function incorrectly<br />');
//add any type of hardware
$container['func'] = 'Hard_add';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<input type="text" name="amount_hard" value="0"><br />');
$PHP_OUTPUT.=('<select name="type_hard">');
$db->query('SELECT * FROM hardware_type ORDER BY hardware_type_id');
while ($db->nextRecord()) {
	$id = $db->getField('hardware_type_id');
	$name = $db->getField('hardware_name');
	$PHP_OUTPUT.=('<option value='.$id.'>'.$name.'</option>');
}
$PHP_OUTPUT.=('</select>&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Set hardware');
$PHP_OUTPUT.=('</form>');
$PHP_OUTPUT.=('<br />Modify Personal Relations <small>note: DO NOT make this less than -500 or greater than 500!</small><br />');

//change personal relations
$container['func'] = 'Relations';
$db->query('SELECT * FROM race WHERE race_id > 1 ORDER BY race_id');
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<select name=race>');
while ($db->nextRecord())
	$PHP_OUTPUT.=('<option value="' . $db->getField('race_id') . '">' . $db->getField('race_name') . '</option>');
$PHP_OUTPUT.=('</select>&nbsp;&nbsp;');
$PHP_OUTPUT.=('<input name="amount" value="0">');
$PHP_OUTPUT.=create_submit('Change Relations');
$PHP_OUTPUT.=('</form>');

$PHP_OUTPUT.=('<br />Modify Racial Relations <small>note: DO NOT make this less than -500 or greater than 500!</small><br />');

//change race relations
$container['func'] = 'Race_Relations';
$db->query('SELECT * FROM race WHERE race_id > 1 ORDER BY race_id');
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<select name="race">');
while ($db->nextRecord())
	$PHP_OUTPUT.=('<option value="' . $db->getField('race_id') . '">' . $db->getField('race_name') . '</option>');
$PHP_OUTPUT.=('</select>&nbsp;&nbsp;');
$PHP_OUTPUT.=('<input name="amount" value="0">');
$PHP_OUTPUT.=create_submit('Change Relations');
$PHP_OUTPUT.=('</form>');


$PHP_OUTPUT.=('<br />Modify Race<br />');

//change race
$container['func'] = 'Race';
$db->query('SELECT * FROM race WHERE race_id > 1 ORDER BY race_id');
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<select name="race">');
while ($db->nextRecord())
	$PHP_OUTPUT.=('<option value="' . $db->getField('race_id') . '">' . $db->getField('race_name') . '</option>');
$PHP_OUTPUT.=('</select>&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Change Race');
$PHP_OUTPUT.=('</form>');

?>