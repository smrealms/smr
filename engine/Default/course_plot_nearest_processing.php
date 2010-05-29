<?php

if(isset($var['RealX']))
{
	$realX = $var['RealX'];
}
else
{
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
			if(is_numeric($X))
				$realX =& SmrLocation::getLocation($X);
			else
				$realX = $X;
		break;
		case 'Goods': 
			$realX =& Globals::getGood($X);
		break;
		default:
			create_error('Invalid search');
	}
	
	$account->log(5, 'Player plots to nearest '.$xType.': '.$X.'.', $player->getSectorID());
}


$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'course_plot_result.php';

$sector =& SmrSector::getSector($player->getGameID(),$player->getSectorID());
if($sector->hasX($realX))
	create_error('Current sector has what you\'re looking for');

require_once(get_file_loc('Plotter.class.inc'));
$path =& Plotter::findDistanceToX($realX, $sector, true, $player);
if($path===false)
	create_error('Unable to find what you\'re looking for, it either hasn\'t been added to this game or you haven\'t explored it yet.');

$container['Distance'] = serialize($path);

$path->removeStart();
if ($sector->isLinked($path->getNextOnPath())&&$path->getTotalSectors()>0)
{
	$player->setPlottedCourse($path);
}
forward($container);

?>