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

if($sector->hasX($realX,$player))
	create_error('Current sector has what you\'re looking for!');

$path = Plotter::findReversiblePathToX($realX, $sector, true, $player, $player);

// Forward to do common processing of path
$container = create_container('skeleton.php', 'course_plot_result.php');
$container['Distance'] = serialize($path);
forward($container);
