<?php
try
{
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
	
	if(isset($_REQUEST['sector_id']))
	{
		try
		{
			$galaxy =& SmrGalaxy::getGalaxyContaining(SmrSession::$game_id,$_REQUEST['sector_id']);
		}
		catch(Exception $e)
		{
			header('location: ' . URL . '/error.php?msg=Invalid galaxy id');
			exit;
		}
	}
	else if(isset($_REQUEST['galaxy_id']))
	{
		try
		{
			$galaxy =& SmrGalaxy::getGalaxy(SmrSession::$game_id,$_REQUEST['galaxy_id']);
		}
		catch(Exception $e)
		{
			header('location: ' . URL . '/error.php?msg=Invalid galaxy id');
			exit;
		}
	}
	
	$player	=& SmrPlayer::getPlayer(SmrSession::$account_id, SmrSession::$game_id);
	
	// create account object
	$account =& SmrAccount::getAccount(SmrSession::$account_id);
	
	echo '
	<!DOCTYPE HTML PUBliC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/1999/REC-html401-19991224/loose.dtd">
	';
	
	echo('<html>');
	echo('<head>');
	echo('<link rel="stylesheet" type="text/css" href="css/default.css">');
	echo('<title>Galaxy Map</title>');
	echo('<meta http-equiv="pragma" content="no-cache">');
	echo '<!--[if IE]>
	<link rel="stylesheet" type="text/css" href="css/ie_specific.css">
	<![endif]-->
	<style type="text/css">
		body {
			font-size:' . $account->getFontSize() . '%;
		}
	</style>
	';
	echo('</head>');
	
	echo('<body>');
	
	echo('<h1>View Galaxy</h1>');
	
	if (!isset($_REQUEST['galaxy_id']) && !isset($_REQUEST['sector_id']))
	{
		$galaxy =& SmrGalaxy::getGalaxyContaining(SmrSession::$game_id,$player->getSectorID());
	}
	$gameGals =& SmrGalaxy::getGameGalaxies($player->getGameID());
	echo '<br/><form name="GalaxyMapForm" method="GET"><select name="galaxy_id" onchange="this.form.submit()">';
	foreach($gameGals as &$gameGalaxy)
	{
		$galaxyID		= $gameGalaxy->getGalaxyID();
	
		echo('<option value="'.$galaxyID.'"'.($galaxy->equals($gameGalaxy)?' selected="selected"':'').'>');
		echo('<a href="'.URL.'/map_galaxy.php?galaxy_id='.$galaxyID.'">'.$gameGalaxy->getName().'</a>');
		echo('</option>');
	} unset($gameGalaxy);
	echo '</select> <input type="submit" value="View"/></form>';
	
	$galaxyID = $galaxy->getGalaxyID();
	$ship =& $player->getShip();
	
	$template->assignByRef('ThisSector',SmrSector::getSector($player->getGameID(),$player->getSectorID()));
	
	
	$template->assign('GalaxyName',$galaxy->getName());
	
	if($account->isCenterGalaxyMapOnPlayer() || isset($_REQUEST['sector_id']))
	{
		if(isset($_REQUEST['sector_id']))
			$topLeft =& SmrSector::getSector(SmrSession::$game_id,$_REQUEST['sector_id']);
		else
			$topLeft =& $player->getSector();
		
		if(!$galaxy->contains($topLeft->getSectorID()))
			$topLeft =& SmrSector::getSector($player->getGameID(),$galaxy->getStartSector());
		else
		{
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
	for ($i=0;$i<$galaxy->getHeight();$i++)
	{
		$mapSectors[$i] = array();
		//new row
		if ($i!=0) $leftMostSec =& $leftMostSec->getNeighbourSector('Down');
		
		//get left most sector for this row
		$thisSec =& $leftMostSec;
		//iterate through the columns
		for ($j=0;$j<$galaxy->getWidth();$j++)
		{
			//new sector
			if ($j!=0) $thisSec =& $thisSec->getNeighbourSector('Right');
			$mapSectors[$i][$j] =& $thisSec;
		}
	}
	$template->assignByRef('MapSectors',$mapSectors);
	$template->assignByRef('ThisShip',$ship);
	$template->assignByRef('ThisPlayer',$player);
	$template->display('GalaxyMap.inc');
}
catch(Exception $e)
{
	handleException($e);
}
?>