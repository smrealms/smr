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
	
		header('Location: '.URL.'/login.php');
		exit;
	
	}
	
	if(isset($_REQUEST['sector_id'])) {
		if(($galaxy = SmrGalaxy::getGalaxyContaining(SmrSession::$game_id, $_REQUEST['sector_id'])) === false) {
			header('location: ' . URL . '/error.php?msg=Invalid sector id');
			exit;
		}
	}
	else if(isset($_REQUEST['galaxy_id'])) {
		try {
			$galaxy =& SmrGalaxy::getGalaxy(SmrSession::$game_id,$_REQUEST['galaxy_id']);
		}
		catch(Exception $e) {
			header('location: ' . URL . '/error.php?msg=Invalid galaxy id');
			exit;
		}
	}
	
	$player	=& SmrPlayer::getPlayer(SmrSession::$account_id, SmrSession::$game_id);
	
	// create account object
	$account =& $player->getAccount();
	
	if (!isset($_REQUEST['galaxy_id']) && !isset($_REQUEST['sector_id'])) {
		$galaxy =& SmrGalaxy::getGalaxyContaining(SmrSession::$game_id,$player->getSectorID());
	}
	
	
	$template->assign('GalaxyName',$galaxy->getName());
	
	if($account->isCenterGalaxyMapOnPlayer() || isset($_REQUEST['sector_id'])) {
		if(isset($_REQUEST['sector_id']))
			$topLeft =& SmrSector::getSector($player->getGameID(),$_REQUEST['sector_id']);
		else
			$topLeft =& $player->getSector();
		
		if(!$galaxy->contains($topLeft->getSectorID()))
			$topLeft =& SmrSector::getSector($player->getGameID(),$galaxy->getStartSector());
		else {
			$template->assign('FocusSector', $topLeft->getSectorID());
			//go left then up
			for ($i=0;$i<floor($galaxy->getWidth()/2);$i++)
				$topLeft =& $topLeft->getNeighbourSector('Left');
			for ($i=0;$i<floor($galaxy->getHeight()/2);$i++)
				$topLeft =& $topLeft->getNeighbourSector('Up');
		}
	}
	else
		$topLeft =& SmrSector::getSector($player->getGameID(), $galaxy->getStartSector());
	
	$mapSectors = array();
	$leftMostSec =& $topLeft;
	for ($i=0;$i<$galaxy->getHeight();$i++) {
		$mapSectors[$i] = array();
		//new row
		if ($i!=0) $leftMostSec =& $leftMostSec->getNeighbourSector('Down');
		
		//get left most sector for this row
		$thisSec =& $leftMostSec;
		//iterate through the columns
		for ($j=0;$j<$galaxy->getWidth();$j++) {
			//new sector
			if ($j!=0) $thisSec =& $thisSec->getNeighbourSector('Right');
			$mapSectors[$i][$j] =& $thisSec;
		}
	}
	
	if($account->getCssLink()!=null)
		$template->assign('ExtraCSSLink',$account->getCssLink());
	$template->assign('CSSLink',URL.'/css/'.$account->getTemplate().'.css');
	$template->assign('CSSColourLink',URL.'/css/'.$account->getTemplate().'/'.$account->getColourScheme().'.css');
	$template->assignByRef('ThisGalaxy',$galaxy);
	$template->assignByRef('ThisAccount',$account);
	$template->assignByRef('GameGalaxies',SmrGalaxy::getGameGalaxies($player->getGameID()));
	$template->assignByRef('ThisSector',$player->getSector());
	$template->assignByRef('MapSectors',$mapSectors);
	$template->assignByRef('ThisShip',$player->getShip());
	$template->assignByRef('ThisPlayer',$player);
	$template->display('GalaxyMap.inc');
}
catch(Exception $e) {
	handleException($e);
}
?>