<?php
require_once(get_file_loc('SmrGalaxy.class.inc'));

if (isset($var['gal_on'])) $gal_on = $var['gal_on'];
else $PHP_OUTPUT.= 'Gal_on not found!!';

$galaxy =& SmrGalaxy::getGalaxy($var['game_id'],$var['gal_on']);
$galSectors =& $galaxy->getSectors();
//get totals
$numberOfPlanets=0;
foreach ($galSectors as &$galSector) {
	if($galSector->hasPlanet()) {
		$numberOfPlanets++;
	}
}

//universe_create_planets.php
$container = $var;
$container['url'] = '1.6/universe_create_save_processing.php';
$container['body'] = '1.6/universe_create_sectors.php';
$PHP_OUTPUT.= create_echo_form($container);
		
$galaxy =& SmrGalaxy::getGalaxy($var['game_id'],$var['gal_on']);
$PHP_OUTPUT.= 'Working on Galaxy : ' . $galaxy->getName() . ' (' . $galaxy->getGalaxyID() . ')<br />';
$PHP_OUTPUT.= '<table class="standard">';
$PHP_OUTPUT.= '<tr><td class="right">Uninhabited Planets</td><td class="left">';
$PHP_OUTPUT.= '<input type="text" value="' . $numberOfPlanets . '" size="5" name="Uninhab"></td></tr>';
$PHP_OUTPUT.= '<tr><td class="right">NPC Planets - Won\'t work</td><td class="left">';
$PHP_OUTPUT.= '<input type="text" value="' . (isset($planet_info['NPC']) ? $planet_info['NPC'] : 0) . '" size="5" name="NPC"></td></tr>';
$PHP_OUTPUT.= '<tr><td colspan="2" class="center"><input type="submit" name="submit" value="Create Planets">';
$container = $var;
$container['body'] = '1.6/universe_create_sectors.php';
$PHP_OUTPUT.= '<br /><br /><a href="'.SmrSession::getNewHREF($container).'" class="submitStyle">Cancel</a>';
$PHP_OUTPUT.= '</td></tr></table></form>';

$PHP_OUTPUT.= '<span class="small">Note: When you press "Create Planets" this will rearrange all current planets.<br />';
$PHP_OUTPUT.= 'To add new planets without rearranging everything use the edit sector feature.</span>';
?>