<?php
try {
	// ********************************
	// *
	// * I n c l u d e s   h e r e
	// *
	// ********************************
	
	require_once('config.inc');
	require_once(LIB . 'Default/SmrMySqlDatabase.class.inc');
	require_once(LIB . 'Default/Globals.class.inc');
	require_once(get_file_loc('smr.inc'));
	require_once(get_file_loc('SmrAccount.class.inc'));
	require_once(get_file_loc('SmrPlayer.class.inc'));
	require_once(get_file_loc('SmrSector.class.inc'));
	require_once(get_file_loc('SmrSession.class.inc'));
	require_once(get_file_loc('SmrGalaxy.class.inc'));
	
	// avoid site caching
	header('Expires: Mon, 03 Nov 1976 16:10:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') .' GMT');
	header('Cache-Control: no-cache');
	header('Pragma: no-cache');
	header('Cache-Control: post-check=0, pre-check=0', FALSE);
	
	// ********************************
	// *
	// * S e s s i o n
	// *
	// ********************************
	
	
	// do we have a session?
	if (SmrSession::$account_id == 0 || SmrSession::$game_id == 0) {
	
		header('Location: /login.php');
		exit;
	
	}
	
	if(isset($_REQUEST['sector_id'])) {
		$sectorID = $_REQUEST['sector_id'];
		if(!is_numeric($sectorID)) {
			header('location: /error.php?msg=Sector ID was not a number.');
			exit;
		}
		try {
			$galaxy = SmrGalaxy::getGalaxyContaining(SmrSession::$game_id, $sectorID);
		} catch (SectorNotFoundException $e) {
			header('location: /error.php?msg=Invalid sector ID');
			exit;
		}
	}
	else if(isset($_REQUEST['galaxy_id'])) {
		$galaxyID = $_REQUEST['galaxy_id'];
		if(!is_numeric($galaxyID)) {
			header('location: /error.php?msg=Galaxy ID was not a number.');
			exit;
		}
		try {
			$galaxy = SmrGalaxy::getGalaxy(SmrSession::$game_id,$galaxyID);
		}
		catch(Exception $e) {
			header('location: /error.php?msg=Invalid galaxy ID');
			exit;
		}
	}
	
	$player = SmrPlayer::getPlayer(SmrSession::$account_id, SmrSession::$game_id);
	
	// create account object
	$account = $player->getAccount();
	
	// Create a session to store temporary display options
	// Garbage collect here often, since the page is slow anyways (see map_local.php)
	if (!session_start(['gc_probability' => 10, 'gc_maxlifetime' => 86400])) {
		throw new Exception('Failed to start session');
	}

	// Set temporary options
	if ($player->hasAlliance()) {
		if (isset($_POST['change_settings'])) {
			$_SESSION['show_seedlist_sectors'] = isset($_POST['show_seedlist_sectors']);
			$_SESSION['hide_allied_forces'] = isset($_POST['hide_allied_forces']);
		}
		$showSeedlistSectors = isset($_SESSION['show_seedlist_sectors']) ? $_SESSION['show_seedlist_sectors'] : false;
		$hideAlliedForces = isset($_SESSION['hide_allied_forces']) ? $_SESSION['hide_allied_forces'] : false;
		$template->assign('ShowSeedlistSectors', $showSeedlistSectors);
		$template->assign('HideAlliedForces', $hideAlliedForces);
		$template->assign('CheckboxFormHREF', ''); // Submit to same page
	}

	if (!isset($galaxyID) && !isset($sectorID)) {
		$galaxy = SmrGalaxy::getGalaxyContaining(SmrSession::$game_id,$player->getSectorID());
		if ($account->isCenterGalaxyMapOnPlayer()) {
			$sectorID = $player->getSectorID();
		}
	}

	$galaxy->getSectors(); //optimized call to cache all sectors first
	if (isset($sectorID)) {
		$template->assign('FocusSector', $sectorID);
		$mapSectors = $galaxy->getMapSectors($sectorID);
	} else {
		$mapSectors = $galaxy->getMapSectors();
	}
	
	if($account->getCssLink()!=null)
		$template->assign('ExtraCSSLink',$account->getCssLink());
	$template->assign('Title', 'Galaxy Map');
	$template->assign('CSSLink', $account->getCssUrl());
	$template->assign('CSSColourLink', $account->getCssColourUrl());
	$template->assign('FontSize', $account->getFontSize() - 20);
	$template->assign('ThisGalaxy',$galaxy);
	$template->assign('ThisAccount',$account);
	$template->assign('GameGalaxies',SmrGalaxy::getGameGalaxies($player->getGameID()));
	$template->assign('ThisSector',$player->getSector());
	$template->assign('MapSectors',$mapSectors);
	$template->assign('ThisShip',$player->getShip());
	$template->assign('ThisPlayer',$player);

	// AJAX updates are not set up for the galaxy map at this time
	$template->assign('AJAX_ENABLE_REFRESH', false);

	$template->display('GalaxyMap.inc');
}
catch(Throwable $e) {
	handleException($e);
}
