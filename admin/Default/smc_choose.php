<?php

$template->assign('PageTopic','Generate SMC Files');

$container = array();
$container['url'] = 'smc_new.php';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<select name="game_id" id="InputFields">');
$db->query('SELECT sector.game_id as sec, game_name FROM sector, game WHERE sector.game_id = game.game_id AND enabled = \'TRUE\' GROUP BY sector.game_id');
while($db->nextRecord()) {

	$id = $db->getField('sec');
	$name = $db->getField('game_name');
	$PHP_OUTPUT.=('<option value="'.$id.'">'.$name.'</option>');

}
$PHP_OUTPUT.=('</select>');
$PHP_OUTPUT.=('&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Create \'Sectors.smc\'');
$PHP_OUTPUT.=('</form>');

$container = array();
$container['url'] = 'smc_create_ini.php';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<select name="game_id" id="InputFields">');
$db->query('SELECT sector.game_id as sec, game_name FROM sector, game WHERE sector.game_id = game.game_id AND enabled = \'TRUE\' GROUP BY sector.game_id');
while($db->nextRecord()) {

	$id = $db->getField('sec');
	$name = $db->getField('game_name');
	$PHP_OUTPUT.=('<option value="'.$id.'">'.$name.'</option>');

}
$PHP_OUTPUT.=('</select>');
$PHP_OUTPUT.=('&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('Create \'Game.ini\'');
$PHP_OUTPUT.=('</form>');

////new file type
//$container = array();
//$container['url'] = 'smc_new.php';
//$PHP_OUTPUT.=create_echo_form($container);
//$PHP_OUTPUT.=('<select name="game_id" id="InputFields">');
//$db->query('SELECT sector.game_id as sec, game_name FROM sector, game WHERE sector.game_id = game.game_id AND enabled = \'TRUE\' GROUP BY sector.game_id');
//while($db->nextRecord()) {
//
//	$id = $db->getField('sec');
//	$name = $db->getField('game_name');
//	$PHP_OUTPUT.=('<option value="'.$id.'">'.$name.'</option>');
//
//}
//$PHP_OUTPUT.=('</select>');
//$PHP_OUTPUT.=('&nbsp;&nbsp;');
//$PHP_OUTPUT.=create_submit('Create New SMC File');
//$PHP_OUTPUT.=('</form>');
