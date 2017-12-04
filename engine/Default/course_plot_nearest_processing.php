<?php
require_once(get_file_loc('Plotter.class.inc'));

if(isset($var['RealX'])) {
	$realX = $var['RealX'];
}
else {
	if (!isset($_REQUEST['xtype']) || !isset($_REQUEST['X']))
		create_error('You have to select what you would like to find.');
	$xType = $_REQUEST['xtype'];
	$X = $_REQUEST['X'];
	$realX = Plotter::getX($xType, $X, $player->getGameID(), $player);
	if($realX === false) {
		create_error('Invalid search.');
	}

	$account->log(LOG_TYPE_MOVEMENT, 'Player plots to nearest '.$xType.': '.$X.'.', $player->getSectorID());
}


$container = create_container('skeleton.php', 'course_plot_result.php');

$sector =& $player->getSector();
if($sector->hasX($realX,$player))
	create_error('Current sector has what you\'re looking for!');

$path =& Plotter::findDistanceToX($realX, $sector, true, $player, $player);
if($path===false)
	create_error('Unable to find what you\'re looking for, it either hasn\'t been added to this game or you haven\'t explored it yet.');

if($path->getEndSectorID() < $sector->getSectorID()) { //If sector we find is a lower sector id we replot so we always use the plot from lowest to highest sector.
	$path =& Plotter::findDistanceToX($sector, $path->getEndSector(), true);
	$path->reversePath();
}

$container['Distance'] = serialize($path);

$path->removeStart();
if ($sector->isLinked($path->getNextOnPath())&&$path->getTotalSectors()>0) {
	$player->setPlottedCourse($path);
}
forward($container);

?>
