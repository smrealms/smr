<?php

if (isset($var['from'])) $start = $var['from'];
else $start = trim($_POST['from']);
if (isset($var['to'])) $target = $var['to'];
else $target = trim($_POST['to']);

// perform some basic checks on both numbers
if (empty($start) || empty($target))
	create_error('Where do you want to go today?');


if (!is_numeric($start) || !is_numeric($target))
	create_error('Please enter only numbers!');

$start = abs(str_replace('.','',$start));
$target = abs(str_replace('.','',$target));

if ($start == $target)
	create_error('Hmmmm...if ' . $start . '=' . $target . ' then that means...YOU\'RE ALREADY THERE! *cough*you\'re real smart*cough*');

$startExists = false;
$targetExists = false;
$galaxies =& SmrGalaxy::getGameGalaxies($player->getGameID());
foreach($galaxies as &$galaxy)
{
	if($galaxy->contains($start))
		$startExists = true;
	if($galaxy->contains($target))
		$targetExists = true;
} unset($galaxy);

if($startExists===false || $targetExists===false)
	create_error('The sectors have to exist!');

$account->log(5, 'Player plots to '.$target.'.', $player->getSectorID());

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'course_plot_result.php';

require_once(get_file_loc('Plotter.class.inc'));
$path =& Plotter::findDistanceToX(SmrSector::getSector($player->getGameID(),max($target,$start)), SmrSector::getSector($player->getGameID(),min($target,$start)), true);
if($path===false)
{
	create_error('Unable to plot from '.$start.' to '.$target.'.');
}
if($start > $target) //We always plot lowest to highest, so reverse if need be.
{
	$path->reversePath();
}
$container['Distance'] = serialize($path);

$path->removeStart();
if ($sector->isLinked($path->getNextOnPath())&&$path->getTotalSectors()>0)
{
	$player->setPlottedCourse($path);
}
forward($container);

?>