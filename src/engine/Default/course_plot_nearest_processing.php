<?php declare(strict_types=1);

use Smr\PlotGroup;

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();
$sector = $player->getSector();

if (isset($var['RealX'])) {
	// This is only used by NPC's
	$realX = $var['RealX'];
} else {
	$xType = PlotGroup::from(Smr\Request::get('xtype'));
	$X = Smr\Request::get('X');
	$realX = Plotter::getX($xType, $X, $player->getGameID(), $player);

	$player->log(LOG_TYPE_MOVEMENT, 'Player plots to nearest ' . $xType->value . ': ' . $X . '.');
}

if ($sector->hasX($realX, $player)) {
	create_error('Current sector has what you\'re looking for!');
}

$path = Plotter::findReversiblePathToX($realX, $sector, true, $player, $player);

require_once(LIB . 'Default/course_plot.inc.php');
course_plot_forward($player, $path);
