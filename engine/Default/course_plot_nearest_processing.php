<?

if (!isset($_REQUEST['xtype']) || !isset($_REQUEST['X']))
	create_error('You have to select what you would like to find');
$xType = $_REQUEST['xtype'];
$X = $_REQUEST['X'];

switch($xType)
{
	case 'Technology':
		$realX =& Globals::getHardwareTypes($X);
	break;
	case 'Ships':
		$realX =& AbstractSmrShip::getBaseShip(Globals::getGameType(SmrSession::$game_id),$X);
	break;
	case 'Weapons':
		$realX =& SmrWeapon::getWeapon(Globals::getGameType(SmrSession::$game_id),$X);
	break;
	case 'Locations':
		$realX =& SmrLocation::getLocation($X);
	break;
	case 'Goods': 
		$realX =& Globals::getGood($X);
	break;
	default:
		create_error('Invalid search');
}

$account->log(5, 'Player plots to nearest '.$xType.': '.$X.'.', $player->getSectorID());

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'course_plot_result.php';

require_once(get_file_loc('Plotter.class.inc'));
$path =& Plotter::findDistanceToX($realX, SmrSector::getSector($player->getGameID(),$player->getSectorID()), true, $player);
if($path===false)
	create_error('Unable to find the nearest X');
$container['Distance'] = serialize($path);

forward($container);

?>