<?php
require_once(get_file_loc('SmrGalaxy.class.inc'));

$galaxies =& SmrGalaxy::getGameGalaxies($var['game_id']);
$galaxy =& SmrGalaxy::getGalaxy($var['game_id'],$var['gal_on']);
$galSectors =& $galaxy->getSectors();

// Initialize warps array
$warps = array();
foreach ($galaxies as $otherGalaxy) {
	$warps[$otherGalaxy->getGalaxyID()] = 0;
}

//get totals
foreach ($galSectors as &$galSector) {
	if($galSector->hasWarp()) {
		$otherGalaxyID = $galSector->getWarpSector()->getGalaxyID();
		if($otherGalaxyID==$galaxy->getGalaxyID())
			$warps[$otherGalaxyID]+=0.5;
		else
			$warps[$otherGalaxyID]++;
	}
}

$container = $var;
$container['url'] = '1.6/universe_create_save_processing.php';
$container['body'] = '1.6/universe_create_sectors.php';
$template->assign('SubmitHREF', SmrSession::getNewHREF($container));

$container = $var;
$container['body'] = '1.6/universe_create_sectors.php';
$template->assign('CancelHREF', SmrSession::getNewHREF($container));

$template->assign('Galaxy', $galaxy);
$template->assign('Galaxies', $galaxies);
$template->assign('Warps', $warps);

?>
