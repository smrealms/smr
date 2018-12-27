<?php

if (!isset($var['gal_on'])) {
	throw Exception('Gal_on not found!');
}

$galaxy = SmrGalaxy::getGalaxy($var['game_id'],$var['gal_on']);
$template->assign('Galaxy', $galaxy);

//get totals
$total = array();
$totalPorts = array();
$total['Ports'] = 0;
for ($i=1; $i<=SmrPort::MAX_LEVEL; $i++) {
	$totalPorts[$i] = 0;
}
foreach ($galaxy->getSectors() as $galSector) {
	if($galSector->hasPort()) {
		$totalPorts[$galSector->getPort()->getLevel()]++;
		$total['Ports']++;
	}
}

$container = $var;
$container['url'] = '1.6/universe_create_save_processing.php';
$container['body'] = '1.6/universe_create_sectors.php';
$template->assign('CreateHREF', SmrSession::getNewHREF($container));

$template->assign('Total', $total);
$template->assign('TotalPorts', $totalPorts);
