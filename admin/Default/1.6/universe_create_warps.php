<?php
require_once(get_file_loc('SmrGalaxy.class.inc'));

if (isset($var['message'])) {
	$template->assign('Message', $var['message']);
	SmrSession::updateVar('message', null); // Only show message once
}

$galaxies =& SmrGalaxy::getGameGalaxies($var['game_id']);
$galaxy =& SmrGalaxy::getGalaxy($var['game_id'],$var['gal_on']);
$galSectors =& $galaxy->getSectors();

$template->assign('PageTopic', 'Warps for Galaxy : '.$galaxy->getName().' ('.$galaxy->getGalaxyID().')');


// Initialize warps array
$warps = array();
foreach ($galaxies as $gal1) {
	$warps[$gal1->getGalaxyID()] = array();
	foreach ($galaxies as $gal2) {
		$warps[$gal1->getGalaxyID()][$gal2->getGalaxyID()] = 0;
	}
}

//get totals
$db->query('SELECT * FROM warp WHERE game_id='.$db->escapeNumber($var['game_id']));
while ($db->nextRecord()) {
	$warp1 = SmrSector::getSector($db->getInt('game_id'), $db->getInt('sector_id_1'));
	$warp2 = SmrSector::getSector($db->getInt('game_id'), $db->getInt('sector_id_2'));
	if ($warp1->getGalaxyID() == $warp2->getGalaxyID()) {
		$warps[$warp1->getGalaxyID()][$warp2->getGalaxyID()]++;
	} else {
		$warps[$warp1->getGalaxyID()][$warp2->getGalaxyID()]++;
		$warps[$warp2->getGalaxyID()][$warp1->getGalaxyID()]++;
	}
}

// Get links to other pages
$container = create_container('skeleton.php', '1.6/universe_create_warps.php');
$container['game_id'] = $var['game_id'];
$galLinks = array();
foreach ($galaxies as $gal) {
	$container['gal_on'] = $gal->getGalaxyID();
	$galLinks[$gal->getGalaxyID()] = SmrSession::getNewHREF($container);
}
$template->assign('GalLinks', $galLinks);

$container = $var;
$container['url'] = '1.6/universe_create_save_processing.php';
$container['body'] = '1.6/universe_create_warps.php';
$template->assign('SubmitHREF', SmrSession::getNewHREF($container));

$container = $var;
$container['body'] = '1.6/universe_create_sectors.php';
$template->assign('CancelHREF', SmrSession::getNewHREF($container));

$template->assign('Galaxy', $galaxy);
$template->assign('Galaxies', $galaxies);
$template->assign('Warps', $warps);

?>
