<?php
require_once(get_file_loc('SmrGalaxy.class.inc'));

if (isset($var['gal_on'])) $gal_on = $var['gal_on'];
else $PHP_OUTPUT.= 'Gal_on not found!!';

$galaxies =& SmrGalaxy::getGameGalaxies($var['game_id']);
$galaxy =& SmrGalaxy::getGalaxy($var['game_id'],$var['gal_on']);
$galSectors =& $galaxy->getSectors();
//get totals
foreach ($galSectors as &$galSector) {
	if($galSector->hasWarp()) {
		$otherGalaxyID = $galSector->getWarpSector()->getGalaxyID();
		if($otherGalaxyID==$galaxy->getGalaxyID())
			$warps[$otherGalaxyID]+=0.5;
		else
			$warps[$otherGalaxyID]++;
	}
}

//universe_create_warps.php
$container = $var;
$container['url'] = '1.6/universe_create_save_processing.php';
$container['body'] = '1.6/universe_create_sectors.php';
$PHP_OUTPUT.= create_echo_form($container);
$PHP_OUTPUT.= 'Working on Galaxy : ' . $galaxy->getName() . ' (' . $galaxy->getGalaxyID() . ')<br />';
$PHP_OUTPUT.= '<table class="standard">';
foreach ($galaxies as &$eachGalaxy) {
	$PHP_OUTPUT.= '<tr><td class="right">' . $eachGalaxy->getName() . '</td><td class="left">';
	$PHP_OUTPUT.= '<input type="text" value="';
	if (isset($warps[$eachGalaxy->getGalaxyID()])) $PHP_OUTPUT.= $warps[$eachGalaxy->getGalaxyID()];
	else $PHP_OUTPUT.= '0';
	$PHP_OUTPUT.= '" size="5" name="warp' . $eachGalaxy->getGalaxyID() . '"></td></tr>';
}
$PHP_OUTPUT.= '<tr><td colspan="2" class="center"><input type="submit" name="submit" value="Create Warps">';
$container = $var;
$container['body'] = '1.6/universe_create_sectors.php';
$PHP_OUTPUT.= '<br /><br /><a href="'.SmrSession::getNewHREF($container).'" class="submitStyle">Cancel</a>';
$PHP_OUTPUT.= '</td></tr></table></form>';

$PHP_OUTPUT.= '<span class="small">Note: When you press "Create Warps" this will rearrange all current warps.<br />';
$PHP_OUTPUT.= 'To add new warps without rearranging everything use the edit sector feature.';
$PHP_OUTPUT.= 'Keep in mind this removes both sides of the warp, so 2 gals are changed for each warp.</span>';

?>