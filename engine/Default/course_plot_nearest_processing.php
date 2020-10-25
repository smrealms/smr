<?php declare(strict_types=1);

if (isset($var['RealX'])) {
	// This is only used by NPC's
	$realX = $var['RealX'];
} else {
	$xType = Request::get('xtype');
	$X = Request::get('X');
	$realX = Plotter::getX($xType, $X, $player->getGameID(), $player);
	if ($realX === false) {
		create_error('Invalid search.');
	}

	$player->log(LOG_TYPE_MOVEMENT, 'Player plots to nearest ' . $xType . ': ' . $X . '.');
}

if ($sector->hasX($realX, $player)) {
	create_error('Current sector has what you\'re looking for!');
}

$path = Plotter::findReversiblePathToX($realX, $sector, true, $player, $player);

// common processing
require('course_plot_processing.inc');
