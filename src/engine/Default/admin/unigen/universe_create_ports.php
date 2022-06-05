<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$session->getRequestVarInt('gal_on');
$template->assign('Galaxies', SmrGalaxy::getGameGalaxies($var['game_id']));

$container = Page::create('admin/unigen/universe_create_ports.php');
$container->addVar('game_id');
$template->assign('JumpGalaxyHREF', $container->href());

$galaxy = SmrGalaxy::getGalaxy($var['game_id'], $var['gal_on']);
$template->assign('Galaxy', $galaxy);

// initialize totals
$totalPorts = array_fill(1, SmrPort::MAX_LEVEL, 0);
$totalRaces = array_fill_keys(Smr\Race::getAllIDs(), 0);
$racePercents = $totalRaces;

foreach ($galaxy->getSectors() as $galSector) {
	if ($galSector->hasPort()) {
		$totalRaces[$galSector->getPort()->getRaceID()]++;
		$totalPorts[$galSector->getPort()->getLevel()]++;
	}
}
$total = array_sum($totalPorts);

if ($total > 0) {
	foreach ($totalRaces as $raceID => $totalRace) {
		$racePercents[$raceID] = round($totalRace / $total * 100);
	}
}
$template->assign('RacePercents', $racePercents);
$template->assign('TotalPercent', array_sum($racePercents));

$container = Page::create('admin/unigen/universe_create_save_processing.php', $var);
$container['forward_to'] = 'admin/unigen/universe_create_sectors.php';
$template->assign('CreateHREF', $container->href());

$template->assign('TotalPorts', $totalPorts);
$template->assign('Total', array_sum($totalPorts));
