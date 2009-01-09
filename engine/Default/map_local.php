<?
////////////////////////////////////////////////////////////
//
//	Script:		map_local.php
//	Purpose:	Displays Local Map
//
////////////////////////////////////////////////////////////

$db->query('SELECT
sector.galaxy_id as galaxy_id,
galaxy.galaxy_name as galaxy_name
FROM sector,galaxy
WHERE sector.sector_id=' . $player->getSectorID() . '
AND game_id=' . SmrSession::$game_id . '
AND galaxy.galaxy_id = sector.galaxy_id
LIMIT 1');
$db->next_record();

$galaxy_name = $db->f('galaxy_name');
$galaxy_id = $db->f('galaxy_id');

$smarty->assign('GalaxyName',$galaxy_name);

$smarty->assign('HeaderTemplateInclude','includes/LocalMapJS.inc');

$db->query('
SELECT
MIN(sector_id),
COUNT(*)
FROM sector
WHERE galaxy_id=' . $galaxy_id . '
AND game_id=' . SmrSession::$game_id);

$db->next_record();

global $col,$rows,$size,$offset;
$size = $db->f('COUNT(*)');
$col = $rows = sqrt($size);
//echo $db->f('COUNT(*)');
$top_left = $db->f('MIN(sector_id)');
$offset = $top_left -1;
//$current_y = floor(($player->getSectorID() - $start)/$width);
//$current_x = ($player->getSectorID() - $start) % $width;

$zoomOn = false;
if(isset($var['Dir']))
{
	$zoomOn = true;
	if ($var['Dir'] == 'Up')
	{
		$player->decreaseZoom(1);
	}
	elseif ($var['Dir'] == 'Down')
	{
		$player->increaseZoom(1);
	}
}
$dist = $player->getZoom();

$smarty->assign('isZoomOn',$zoomOn);

$container = array();
$container['url'] = 'skeleton.php';
$container['Dir'] = 'Down';
$container['rid'] = 'zoom_down';
$container['body'] = 'map_local.php';
$container['valid_for'] = -5;
$smarty->assign('ZoomDownLink',SmrSession::get_new_href($container));
$container['Dir'] = 'Up';
$container['rid'] = 'zoom_up';
$smarty->assign('ZoomUpLink',SmrSession::get_new_href($container));

$span = 1 + ($dist * 2);
//echo $player->getZoom();

$upLeft = $dist;

//figure out what should be the top left and bottom right
//$col = $GAL_NAMES[$GAL_ID]['Length'];
//$rows = $GAL_NAMES[$GAL_ID]['Height'];
//$size = $col * $rows;
//$sectorKeys=array_keys($SECTOR);
//$first_sec = array_shift($sectorKeys);
//$offset = $first_sec - 1;
$top_left = $player->getSectorID();
//go left then up
for ($i=1;$i<=$upLeft&&$i<=$col/2;$i++)
	$top_left = get_real_left($top_left);
for ($i=1;$i<=$upLeft&&$i<=$rows/2;$i++)
	$top_left = get_real_up($top_left);

$mapSectors = array();
$leftMostSec = $top_left;
for ($i=1;$i<=$span&&$i<=$rows;$i++)
{
	$mapSectors[$i] = array();
	//new row
	if ($i!=1) $leftMostSec = get_real_down($leftMostSec);
	
	//get left most sector for this row
	$this_sec = $leftMostSec;
	//iterate through the columns
	for ($j=1;$j<=$span&&$j<=$col;$j++)
	{
		//new sector
		if ($j!=1) $this_sec = get_real_right($this_sec);
		$mapSectors[$i][$j] =& SmrSector::getSector(SmrSession::$game_id,$this_sec,SmrSession::$account_id);
	}
}
$smarty->assign_by_ref('MapSectors',$mapSectors);

function get_real_up($sector)
{
	global $offset, $size, $col, $rows;
	$sector_check = $sector - $offset;
	if ($sector_check <= $col) $up = $sector + $size - $col;
	else $up = $sector - $col;
	return $up;
}
function get_real_down($sector)
{
	global $offset, $size, $col, $rows;
	$sector_check = $sector - $offset;
	if ($sector_check >= ($size - $col + 1)) $down = $sector - $size + $col;
	else $down = $sector + $col;
	return $down;
}
function get_real_left($sector)
{
	global $offset, $size, $col, $rows;
	$sector_check = $sector - $offset;
	if (($sector_check - 1) % $col == 0) $left = $sector + $col - 1;
	else $left = $sector - 1;
	return $left;
}
function get_real_right($sector)
{
	global $offset, $size, $col, $rows;
	$sector_check = $sector - $offset;
	if ($sector_check % $col == 0) $right = ($sector - $col) + 1;
	else $right = $sector + 1;
	return $right;
}
?>