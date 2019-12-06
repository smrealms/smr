<?php declare(strict_types=1);

if (isset($var['from'])) $start = $var['from'];
else $start = trim($_POST['from']);
if (isset($var['to'])) $target = $var['to'];
else $target = trim($_POST['to']);

// perform some basic checks on both numbers
if (empty($start) || empty($target)) {
	create_error('Where do you want to go today?');
}

if (!is_numeric($start) || !is_numeric($target)) {
	create_error('Please enter only numbers!');
}

$start = abs(str_replace('.', '', $start));
$target = abs(str_replace('.', '', $target));

if ($start == $target) {
	create_error('Hmmmm...if ' . $start . '=' . $target . ' then that means...YOU\'RE ALREADY THERE! *cough*you\'re real smart*cough*');
}

try {
	$startSector = SmrSector::getSector($player->getGameID(), $start);
	$targetSector = SmrSector::getSector($player->getGameID(), $target);
} catch (SectorNotFoundException $e) {
	create_error('The sectors have to exist!');
}

$account->log(LOG_TYPE_MOVEMENT, 'Player plots to ' . $target . '.', $player->getSectorID());

$path = Plotter::findReversiblePathToX($targetSector, $startSector, true);

// common processing
require('course_plot_processing.inc');
