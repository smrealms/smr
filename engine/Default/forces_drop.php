<?php

if (isset($var['owner_id'])) {
	
	$owner =& SmrPlayer::getPlayer($var['owner_id'], SmrSession::$game_id);
	$template->assign('PageTopic','Change '.$owner->getPlayerName().'\'s Forces');
    $owner_id = $var['owner_id'];

} else {

	$template->assign('PageTopic','Drop Forces');
    $owner_id = $player->getAccountID();

}

require_once(get_file_loc('SmrForce.class.inc'));
$forces =& SmrForce::getForce($player->getGameID(), $player->getSectorID(), $owner_id);

$container = array();
$container['url']		= 'forces_drop_processing.php';
$container['owner_id']	= $owner_id;

$PHP_OUTPUT.=create_echo_form($container);

$PHP_OUTPUT.=create_table();
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th align="center">Force</th>');
$PHP_OUTPUT.=('<th align="center">On Ship</th>');
$PHP_OUTPUT.=('<th align="center">In Sector</th>');
$PHP_OUTPUT.=('<th align="center">Drop</th>');
$PHP_OUTPUT.=('<th align="center">Take</th>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Mines</td>');
$PHP_OUTPUT.=('<td align="center">' . $ship->getMines() . '</td>');
$PHP_OUTPUT.=('<td align="center">'.$forces->getMines().'</td>');
$PHP_OUTPUT.=('<td align="center"><input type="text" name="drop_mines" value="0" id="InputFields" style="width:100px;" class="center"></td>');
$PHP_OUTPUT.=('<td align="center"><input type="text" name="take_mines" value="0" id="InputFields" style="width:100px;" class="center"></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Combat Drones</td>');
$PHP_OUTPUT.=('<td align="center">' . $ship->getCDs() . '</td>');
$PHP_OUTPUT.=('<td align="center">'.$forces->getCDs().'</td>');
$PHP_OUTPUT.=('<td align="center"><input type="text" name="drop_combat_drones" value="0" id="InputFields" style="width:100px;" class="center"></td>');
$PHP_OUTPUT.=('<td align="center"><input type="text" name="take_combat_drones" value="0" id="InputFields" style="width:100px;" class="center"></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Scout Drones</td>');
$PHP_OUTPUT.=('<td align="center">' . $ship->getSDs() . '</td>');
$PHP_OUTPUT.=('<td align="center">'.$forces->getSDs().'</td>');
$PHP_OUTPUT.=('<td align="center"><input type="text" name="drop_scout_drones" value="0" id="InputFields" style="width:100px;" class="center"></td>');
$PHP_OUTPUT.=('<td align="center"><input type="text" name="take_scout_drones" value="0" id="InputFields" style="width:100px;" class="center"></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center" colspan="3">&nbsp;</td>');
$PHP_OUTPUT.=('<td align="center" colspan="2">');
$PHP_OUTPUT.=create_submit('Drop/Take');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('</table>');

$PHP_OUTPUT.=('</form>')

?>