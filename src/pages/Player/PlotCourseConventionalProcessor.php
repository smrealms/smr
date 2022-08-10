<?php declare(strict_types=1);

use Smr\Exceptions\SectorNotFound;
use Smr\Request;

		$session = Smr\Session::getInstance();
		$player = $session->getPlayer();

		$start = Request::getVarInt('from');
		$target = Request::getVarInt('to');

		// perform some basic checks on both numbers
		if (empty($start) || empty($target)) {
			create_error('Where do you want to go today?');
		}

		if ($start == $target) {
			create_error('Hmmmm...if ' . $start . '=' . $target . ' then that means...YOU\'RE ALREADY THERE! *cough*you\'re real smart*cough*');
		}

		try {
			$startSector = SmrSector::getSector($player->getGameID(), $start);
			$targetSector = SmrSector::getSector($player->getGameID(), $target);
		} catch (SectorNotFound) {
			create_error('The sectors have to exist!');
		}

		$player->log(LOG_TYPE_MOVEMENT, 'Player plots to ' . $target . '.');

		$path = Plotter::findReversiblePathToX($targetSector, $startSector, true);

		require_once(LIB . 'Default/course_plot.inc.php');
		course_plot_forward($player, $path);
