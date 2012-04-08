<?php
require_once(get_file_loc('SmrGalaxy.class.inc'));

if (!isset($var['game_id'])) SmrSession::updateVar('game_id', $_REQUEST['game_id']);
if (!isset($var['gal_on'])) SmrSession::updateVar('gal_on', 1);

//generate sector array
$galaxy =& SmrGalaxy::getGalaxy($var['game_id'],$var['gal_on']);
$row = $galaxy->getHeight();
$col = $galaxy->getWidth();
$size = $galaxy->getSize();
$offset = $galaxy->getStartSector()-1;

if (isset($_REQUEST['connect']) && $_REQUEST['connect'] > 0)
	SmrSession::updateVar('conn',$_REQUEST['connect']);
else if (!isset($var['conn']))
	SmrSession::updateVar('conn',100);
$connectivity = $var['conn'];

if(isset($var['message']))
	$PHP_OUTPUT.=$var['message'].'<br /><br />';
SmrSession::updateVar('message',null); // Only show message once

$PHP_OUTPUT.= 'Working on Galaxy : ' . $galaxy->getName() . ' (' . $galaxy->getGalaxyID() . ')<br />';
$PHP_OUTPUT.= 'Hover over a sector to get details about that sector.<br />';
$PHP_OUTPUT.= '<table><tr><td colspan="3">';

$PHP_OUTPUT.= '<div class="center"><a href="'.SmrSession::getNewHREF($var).'" class="submitStyle">Refresh</a><br />';

$container = $var;
$container['url'] = '1.6/universe_create_save_processing.php';
$container['body'] = '1.6/universe_create_sectors.php';
$PHP_OUTPUT.= create_echo_form($container);
$PHP_OUTPUT.='<select name="jumpgal">';
$galaxies =& SmrGalaxy::getGameGalaxies($var['game_id']);
foreach($galaxies as &$currGal) {
	$PHP_OUTPUT.= '<option value="'.$currGal->getGalaxyID().'"'.($currGal->equals($galaxy)?'selected="SELECTED"':'').'>'.$currGal->getName().'</option>';
}
$PHP_OUTPUT.='</select>';

$PHP_OUTPUT.='<input type="submit" name="submit" value="Jump To Galaxy"></form></div><br />';
$PHP_OUTPUT.= '<table class="create">';
$container = $var;
$container['body'] = '1.6/universe_create_sector_details.php';
$galSectors =& SmrSector::getGalaxySectors($var['game_id'],$var['gal_on']);
reset($galSectors);
for ($i=0;$i < $row;$i++) {
	$PHP_OUTPUT.= '<tr>';
	for ($j=1;$j <= $col;$j++) {
		list($key, $galSector) = each($galSectors);
		$color = "#xxyyzz";
		$title = '';
		$PHP_OUTPUT.= '<td class="center';
		if (!$galSector->hasLinkLeft()) $PHP_OUTPUT.= ' border_left';
		if (!$galSector->hasLinkDown()) $PHP_OUTPUT.= ' border_bottom';
		if (!$galSector->hasLinkUp()) $PHP_OUTPUT.= ' border_top';
		if (!$galSector->hasLinkRight()) $PHP_OUTPUT.= ' border_right';
		$PHP_OUTPUT.= '"';
		if ($galSector->hasPlanet()) {
//			$sectorPlanet =& $galSector->getPlanet();
			$color = str_replace('yy', 'ff', $color);
//			if ($GAL_PLANETS[$this_sec]['Owner Type'] == 'NPC')
//				$title .= 'NPC';
//			else
			$title .= 'Player';
			$title .= ' Planet';
		}
		if ($galSector->hasPort()) {
			$sectorPort =& $galSector->getPort();
			if ($title != '') $title .= ', ';
			$title .= 'Level ' . $sectorPort->getLevel() . ' Port (' . $sectorPort->getRaceName() . ')';
			$step = round(255 / 9,0);
			$dec = $sectorPort->getLevel() * $step + (10 - $sectorPort->getLevel()) * 2;
			$hex = dechex($dec);
			$step2 = round(60 / 9,0);
			$dec2 = $sectorPort->getLevel() * $step2 + (10 - $sectorPort->getLevel()) * 2;
			$hex2 = dechex($dec2);
			if (strlen($hex) == 1) $hex = '0' . $hex;
			if (strlen($hex2) == 1) $hex2 = '0' . $hex2;
			$color = str_replace('zz', $hex, $color);
			$color = str_replace('xx', $hex2, $color);
		}
		elseif ($galSector->hasPlanet()) $PHP_OUTPUT.= ' style="background-color:#00ff00;"';
		if ($galSector->hasWarp()) {
			if ($title != '') $title .= ', ';
			$title .= 'Warp to ' . $galSector->getWarp();
		}
		if($galSector->hasLocation()) {
			$sectorLocations =& $galSector->getLocations();
			foreach ($sectorLocations as &$sectorLocation) {
			
				if ($title != '') $title .= ', ';
				$title .= $sectorLocation->getName();
			}
		}
		//if nothing was changed with the base change it here for no errors
		$color = str_replace('xx', '00', $color);
		$color = str_replace('yy', '00', $color);
		$color = str_replace('zz', '00', $color);
		$PHP_OUTPUT.= ' style="background-color:' . $color . ';"';
		if ($title != '') $PHP_OUTPUT.= ' title="' . $title . '"';
		$PHP_OUTPUT.= '>';
		
		$container['sector_id'] = $galSector->getSectorID();
		
		$PHP_OUTPUT.=  '<a href="' . SmrSession::getNewHREF($container) . '">';
		if ($galSector->hasLocation()) $PHP_OUTPUT.= '<span class="red">';
		else $PHP_OUTPUT.= '<span>';
		$PHP_OUTPUT .= $galSector->getSectorID() . '</span></a>';
		if ($galSector->hasWarp()) $PHP_OUTPUT.= '<span class="green">*</span>';
		//$link['text'] = $this_sec;
		//create_link($link, $id);
		$PHP_OUTPUT.= '</td>';
	}
	$PHP_OUTPUT.= '</tr>';
}
$PHP_OUTPUT.= '</table>';
$PHP_OUTPUT.= '</td></tr><tr><td class="center vert_cent" width="33%">';
$container = $var;
$container['url'] = '1.6/universe_create_save_processing.php';
$container['body'] = '1.6/universe_create_sectors.php';
$PHP_OUTPUT.= create_echo_form($container);
$PHP_OUTPUT.= 'Connection Percent<br /><input type="text" name="connect" value="' . $connectivity . '" size="3"><br />';
$PHP_OUTPUT.= '<input type="submit" name="submit" value="Redo Connections"></form></td><td class="center vert_cent" width="33%">';
$container = $var;
$container['body'] = '1.6/universe_create_locations.php';
$PHP_OUTPUT.= '<a href="'.SmrSession::getNewHREF($container).'" class="submitStyle">Modify Locations</a><br /><br />';
$container['body'] = '1.6/universe_create_planets.php';
$PHP_OUTPUT.= '<a href="'.SmrSession::getNewHREF($container).'" class="submitStyle">Modify Planets</a><br /><br />';
$container['body'] = '1.6/universe_create_ports.php';
$PHP_OUTPUT.= '<a href="'.SmrSession::getNewHREF($container).'" class="submitStyle">Modify Ports</a><br /><br />';
$container['body'] = '1.6/universe_create_warps.php';
$PHP_OUTPUT.= '<a href="'.SmrSession::getNewHREF($container).'" class="submitStyle">Modify Warps</a><br /><br />';
$PHP_OUTPUT.= '<a href="'.Globals::getSmrFileCreateHREF($var['game_id']).'" class="submitStyle">Create SMR file</a><br /><br />';
$PHP_OUTPUT.= '<br />';
$warning = FALSE;
if ($var['gal_on'] > 1) {
	$container = $var;
	$container['gal_on']--;
	$PHP_OUTPUT.= '<a href="'.SmrSession::getNewHREF($container).'" class="submitStyle">Previous Galaxy</a><br /><br />';
}
if ($var['gal_on'] < count($galaxies)) {
	$container = $var;
	$container['gal_on']++;
	$PHP_OUTPUT.= '<a href="'.SmrSession::getNewHREF($container).'" class="submitStyle">Next Galaxy</a><br />';
}
//else
//{
//	$warning = TRUE;
//	$PHP_OUTPUT.= '<input type="hidden" name="num_sectors" value="'.$this_sec.'">';
//	$PHP_OUTPUT.= '<input type="submit" name="submit" value="Create Universe"><br /><br />';
//	$PHP_OUTPUT.= '<input type="submit" name="submit" value="Create Admins and NPCs">';
//}
$PHP_OUTPUT.= '</td><td class="center vert_cent" width="33%">';
$container = $var;
$container['url'] = '1.6/universe_create_save_processing.php';
$container['body'] = '1.6/universe_create_sectors.php';
$PHP_OUTPUT.= create_echo_form($container);
$PHP_OUTPUT.= 'Sector ID<br /><input type="text" size="5" name="sector_edit"><br /><input type="submit" value="Modify Sector" name="submit">';
$PHP_OUTPUT.= '</form></td></tr><tr><td class="center" colspan="3">';
//if ($warning)
//{
//	$PHP_OUTPUT.= '<span class="small">Note: When you press "Create Universe" ALL universe data will be erased and rewritten.<br />';
//	$PHP_OUTPUT.= 'If you modified ports, goods will be changed at ports in that galaxy (unless you used edit sector)<br />';
//	$PHP_OUTPUT.= 'If you modified planet, NPC planet levels will be reset, player planets will lose their owners (unless you used edit sector)<br />';
//	$PHP_OUTPUT.= 'If you modified ports, mines will be re-inserted.<br />';
//	$PHP_OUTPUT.= 'Use with caution.</span>';
//}
//else
//	$PHP_OUTPUT.= '&nbsp;';
$PHP_OUTPUT.= '</td></tr></table><br /><br />';
$PHP_OUTPUT.= '</form>';

?>