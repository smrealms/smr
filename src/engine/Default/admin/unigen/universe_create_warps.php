<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$db = Smr\Database::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

if (isset($var['message'])) {
	$template->assign('Message', $var['message']);
	unset($var['message']); // Only show message once
}

$galaxies = SmrGalaxy::getGameGalaxies($var['game_id']);
$galaxy = SmrGalaxy::getGalaxy($var['game_id'], $var['gal_on']);

$template->assign('PageTopic', 'Warps for Galaxy : ' . $galaxy->getDisplayName() . ' (' . $galaxy->getGalaxyID() . ')');


// Initialize warps array
$warps = [];
foreach ($galaxies as $gal1) {
	$warps[$gal1->getGalaxyID()] = [];
	foreach ($galaxies as $gal2) {
		$warps[$gal1->getGalaxyID()][$gal2->getGalaxyID()] = 0;
	}
}

//get totals
$dbResult = $db->read('SELECT sector_id, warp FROM sector WHERE warp != 0 AND game_id=' . $db->escapeNumber($var['game_id']));
foreach ($dbResult->records() as $dbRecord) {
	$warp1 = SmrSector::getSector($var['game_id'], $dbRecord->getInt('sector_id'));
	$warp2 = SmrSector::getSector($var['game_id'], $dbRecord->getInt('warp'));
	if ($warp1->getGalaxyID() == $warp2->getGalaxyID()) {
		// For warps within the same galaxy, even though there will be two
		// sectors with warps, we still consider this as "one warp" (pair).
		// Since we're looping over all sectors, we'll hit this twice for each
		// same-galaxy warp pair, so only add 0.5 to avoid double counting.
		$warps[$warp1->getGalaxyID()][$warp2->getGalaxyID()] += 0.5;
	} else {
		$warps[$warp1->getGalaxyID()][$warp2->getGalaxyID()]++;
	}
}

// Get links to other pages
$container = Page::create('admin/unigen/universe_create_warps.php');
$container->addVar('game_id');
$galLinks = [];
foreach ($galaxies as $gal) {
	$container['gal_on'] = $gal->getGalaxyID();
	$galLinks[$gal->getGalaxyID()] = $container->href();
}
$template->assign('GalLinks', $galLinks);

$container = Page::create('admin/unigen/universe_create_save_processing.php', $var);
$container['forward_to'] = 'admin/unigen/universe_create_warps.php';
$template->assign('SubmitHREF', $container->href());

$container = Page::create('admin/unigen/universe_create_sectors.php', $var);
$template->assign('CancelHREF', $container->href());

$template->assign('Galaxy', $galaxy);
$template->assign('Galaxies', $galaxies);
$template->assign('Warps', $warps);
