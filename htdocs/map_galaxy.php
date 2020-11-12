<?php declare(strict_types=1);
try {
	require_once('config.inc');
	require_once(get_file_loc('smr.inc'));
	
	// avoid site caching
	header('Expires: Mon, 03 Nov 1976 16:10:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Cache-Control: no-cache');
	header('Pragma: no-cache');
	header('Cache-Control: post-check=0, pre-check=0', FALSE);
	
	// ********************************
	// *
	// * S e s s i o n
	// *
	// ********************************
	
	
	// do we have a session?
	if (!SmrSession::hasAccount() || !SmrSession::hasGame()) {
		header('Location: /login.php');
		exit;
	}
	
	if (Request::has('sector_id')) {
		$sectorID = Request::getInt('sector_id');
		try {
			$galaxy = SmrGalaxy::getGalaxyContaining(SmrSession::getGameID(), $sectorID);
		} catch (SectorNotFoundException $e) {
			header('location: /error.php?msg=Invalid sector ID');
			exit;
		}
	} elseif (Request::has('galaxy_id')) {
		$galaxyID = Request::getInt('galaxy_id');
		try {
			$galaxy = SmrGalaxy::getGalaxy(SmrSession::getGameID(), $galaxyID);
		} catch (Exception $e) {
			header('location: /error.php?msg=Invalid galaxy ID');
			exit;
		}
	}
	
	$account = SmrSession::getAccount();
	$player = SmrPlayer::getPlayerByAccountID($account->getAccountID(), SmrSession::getGameID());
	
	// Create a session to store temporary display options
	// Garbage collect here often, since the page is slow anyways (see map_local.php)
	if (!session_start(['gc_probability' => 10, 'gc_maxlifetime' => 86400])) {
		throw new Exception('Failed to start session');
	}

	// Initialize the template
	$template = new Template();

	// Set temporary options
	if ($player->hasAlliance()) {
		if (Request::has('change_settings')) {
			$_SESSION['show_seedlist_sectors'] = Request::has('show_seedlist_sectors');
			$_SESSION['hide_allied_forces'] = Request::has('hide_allied_forces');
		}
		$showSeedlistSectors = $_SESSION['show_seedlist_sectors'] ?? false;
		$hideAlliedForces = $_SESSION['hide_allied_forces'] ?? false;
		$template->assign('ShowSeedlistSectors', $showSeedlistSectors);
		$template->assign('HideAlliedForces', $hideAlliedForces);
		$template->assign('CheckboxFormHREF', ''); // Submit to same page
	}

	if (!isset($galaxyID) && !isset($sectorID)) {
		$galaxy = SmrGalaxy::getGalaxyContaining(SmrSession::getGameID(), $player->getSectorID());
		if ($account->isCenterGalaxyMapOnPlayer()) {
			$sectorID = $player->getSectorID();
		}
	}

	// Efficiently construct the caches before proceeding
	$galaxy->getSectors();
	$galaxy->getLocations();
	$galaxy->getPlanets();
	$galaxy->getForces();
	$galaxy->getPlayers();

	if (isset($sectorID)) {
		$template->assign('FocusSector', $sectorID);
		$mapSectors = $galaxy->getMapSectors($sectorID);
	} else {
		$mapSectors = $galaxy->getMapSectors();
	}

	$template->assign('Title', 'Galaxy Map');

	if ($account->getCssLink() != null) {
		$template->assign('ExtraCSSLink', $account->getCssLink());
	}
	$template->assign('CSSLink', $account->getCssUrl());
	$template->assign('CSSColourLink', $account->getCssColourUrl());
	$template->assign('FontSize', $account->getFontSize() - 20);
	$template->assign('ThisGalaxy', $galaxy);
	$template->assign('ThisAccount', $account);
	$template->assign('GameGalaxies', SmrGalaxy::getGameGalaxies($player->getGameID()));
	$template->assign('ThisSector', $player->getSector());
	$template->assign('MapSectors', $mapSectors);
	$template->assign('ThisShip', $player->getShip());
	$template->assign('ThisPlayer', $player);

	// AJAX updates are not set up for the galaxy map at this time
	$template->assign('AJAX_ENABLE_REFRESH', false);

	$template->display('GalaxyMap.inc');
} catch (Throwable $e) {
	handleException($e);
}
