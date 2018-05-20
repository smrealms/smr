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
foreach($galaxies as &$galaxy) {
	if($galaxy->contains($start))
		$startExists = true;
	if($galaxy->contains($target))
		$targetExists = true;
} unset($galaxy);

if($startExists===false || $targetExists===false)
	create_error('The sectors have to exist!');

$account->log(LOG_TYPE_MOVEMENT, 'Player plots to '.$target.'.', $player->getSectorID());

require_once(get_file_loc('Plotter.class.inc'));
$path = Plotter::findReversiblePathToX(SmrSector::getSector($player->getGameID(), $target), SmrSector::getSector($player->getGameID(), $start), true);

// Forward to do common processing of path
$container = create_container('skeleton.php', 'course_plot_result.php');
$container['Distance'] = serialize($path);
forward($container);
