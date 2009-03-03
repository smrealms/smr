<?

$random_salt = mt_rand();

// ********************************
// *
// * I n c l u d e s   h e r e
// *
// ********************************

require_once('config.inc');
require_once(LIB . 'Default/SmrMySqlDatabase.class.inc');
require_once(ENGINE . 'Default/smr.inc');
require_once(get_file_loc('SmrAccount.class.inc'));
require_once(get_file_loc('SmrPlayer.class.inc'));
require_once(get_file_loc('SmrSector.class.inc'));
require_once(get_file_loc('SmrSession.class.inc'));

// avoid site caching
header('Expires: Mon, 03 Nov 1976 16:10:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') .' GMT');
header('Cache-Control: no-cache');
header('Pragma: no-cache');
header('Cache-Control: post-check=0, pre-check=0', FALSE);

// ********************************
// *
// * S e s s i o n
// *
// ********************************


// do we have a session?
if (SmrSession::$account_id == 0 || SmrSession::$game_id == 0) {

	header('Location: '.URL.'/login.php');
	exit;

}
$player	=& SmrPlayer::getPlayer(SmrSession::$account_id, SmrSession::$game_id);

// create account object
$account =& SmrAccount::getAccount(SmrSession::$account_id);

$db = new SmrMySqlDatabase();

echo '
<!DOCTYPE HTML PUBliC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/1999/REC-html401-19991224/loose.dtd">
';

echo('<html>');
echo('<head>');
echo('<link rel="stylesheet" type="text/css" href="css/default.css">');
echo('<title>Galaxy Map</title>');
echo('<meta http-equiv="pragma" content="no-cache">');
echo '<!--[if IE]>
<link rel="stylesheet" type="text/css" href="css/ie_specific.css">
<![endif]-->
<style type="text/css">
	body {
		font-size:' . $account->fontsize . '%;
	}
</style>
';
echo('</head>');

echo('<body>');

echo('<h1>VIEW GALAXY</h1>');

$galaxy_id = $_GET['galaxy_id'];

if (!isset($galaxy_id)) {
	$galaxy_id = (int)$galaxy_id;
	
	$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID(), SmrSession::$account_id);

	echo('<p>Please choose a galaxy:</p>');
	echo('<ul>');

	$db->query('SELECT * FROM sector NATURAL JOIN galaxy ' .
			   'WHERE game_id = '.$player->getGameID().' ' .
			   'GROUP BY sector.galaxy_id ' .
			   'ORDER BY sector.sector_id');
	while($db->nextRecord()) {

		$galaxy_id		= $db->getField('galaxy_id');
		$galaxy_name	= $db->getField('galaxy_name');

		if ($galaxy_id == $sector->getGalaxyID())
			$galaxy_name = '<b>' . $galaxy_name . '</b>';

		echo('<li>');
		echo('<a href="'.URL.'/map_galaxy.php?galaxy_id='.$galaxy_id.'">'.$galaxy_name.'</a>');
		echo('</li>');

	}

	echo('</ul>');
	echo('</body>');
	echo('</html>');

	exit;

}

$galaxy_id = (int)$galaxy_id;

require_once(get_file_loc('SmrShip.class.inc'));
$ship =& $player->getShip(SmrSession::$game_id,SmrSession::$account_id);

$db->query('SELECT
galaxy.galaxy_id as galaxy_id,
galaxy.galaxy_name as galaxy_name
FROM galaxy
WHERE galaxy.galaxy_id = '.$galaxy_id.'
LIMIT 1');

$db->nextRecord();

$galaxy_name = $db->getField('galaxy_name');
$galaxy_id = $db->getField('galaxy_id');

$template->assign('GalaxyName',$galaxy_name);

$template->assign('HeaderTemplateInclude','includes/LocalMapJS.inc');

$template->assign('PlayerHasScanner',$ship->hasScanner());
$template->assignByRef('ThisSector',SmrSector::getSector($player->getGameID(),$player->getSectorID(),$player->getAccountID()));

$db->query('
SELECT
MIN(sector_id),
MAX(sector_id),
COUNT(*)
FROM sector
WHERE galaxy_id=' . $galaxy_id . '
AND game_id=' . SmrSession::$game_id);

$db->nextRecord();

global $col,$rows,$size,$offset;
$size = $db->getField('COUNT(*)');
$col = $rows = sqrt($size);
$dist = $size; //Much bigger than actual map, so will show all
$galaxy_top_left = $db->getField('MIN(sector_id)');
$galaxy_bottom_right = $db->getField('MAX(sector_id)');
$offset = $db->getField('MIN(sector_id)') -1;

$span = 1 + ($dist * 2);
//echo $player->getZoom();

$upLeft = $dist;

$top_left = $player->getSectorID();

if($top_left>$galaxy_bottom_right || $top_left<$galaxy_top_left)
	$top_left = $galaxy_top_left;
else
{
	//go left then up
	for ($i=1;$i<=$upLeft&&$i<=$col/2;$i++)
		$top_left = get_real_left($top_left);
	for ($i=1;$i<=$upLeft&&$i<=$rows/2;$i++)
		$top_left = get_real_up($top_left);
}

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
$template->assignByRef('MapSectors',$mapSectors);
$template->assignByRef('ThisShip',$ship);
$template->assignByRef('ThisPlayer',$player);
$template->display('GalaxyMap.inc');

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
	if ($sector_check % $col == 0) $right = $sector - $col + 1;
	else $right = $sector + 1;
	return $right;
}

?>