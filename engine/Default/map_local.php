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
if(!$db->nextRecord())
	create_error('Could not find sector info');

//enableProtectionDependantRefresh($template,$player);

$galaxy_name = $db->getField('galaxy_name');
$galaxy_id = $db->getField('galaxy_id');

$template->assign('GalaxyName',$galaxy_name);

$template->assign('HeaderTemplateInclude','includes/LocalMapJS.inc');

$db->query('
SELECT
MIN(sector_id),
COUNT(*)
FROM sector
WHERE galaxy_id=' . $galaxy_id . '
AND game_id=' . SmrSession::$game_id);

$db->nextRecord();

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

$template->assign('isZoomOn',$zoomOn);

$container = array();
$container['url'] = 'skeleton.php';
$container['Dir'] = 'Down';
$container['rid'] = 'zoom_down';
$container['body'] = 'map_local.php';
$container['valid_for'] = -5;
$template->assign('ZoomDownLink',SmrSession::get_new_href($container));
$container['Dir'] = 'Up';
$container['rid'] = 'zoom_up';
$template->assign('ZoomUpLink',SmrSession::get_new_href($container));

$span = 1 + ($dist * 2);

$topLeft =& $player->getSector();
$galaxy =& $topLeft->getGalaxy();

//figure out what should be the top left and bottom right
//go left then up
for ($i=0;$i<$dist&&$i<$galaxy->getWidth()/2;$i++)
	$topLeft =& $topLeft->getNeighbourSector('Left');
for ($i=0;$i<$dist&&$i<$galaxy->getHeight()/2;$i++)
	$topLeft =& $topLeft->getNeighbourSector('Up');

$mapSectors = array();
$leftMostSec =& $topLeft;
for ($i=0;$i<$span&&$i<$galaxy->getHeight();$i++)
{
	$mapSectors[$i] = array();
	//new row
	if ($i!=0) $leftMostSec =& $leftMostSec->getNeighbourSector('Down');
	
	//get left most sector for this row
	$thisSec =& $leftMostSec;
	//iterate through the columns
	for ($j=0;$j<$span&&$j<$galaxy->getWidth();$j++)
	{
		//new sector
		if ($j!=0) $thisSec =& $thisSec->getNeighbourSector('Right');
		$mapSectors[$i][$j] =& $thisSec;
	}
}
$template->assignByRef('MapSectors',$mapSectors);

?>