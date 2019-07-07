<?php

$game = SmrGame::getGame($var['game_id']);
$template->assign('PageTopic', 'Edit Galaxies : ' . $game->getDisplayName());
$template->assign('GameEnabled', $game->isEnabled());

$container = create_container('1.6/galaxies_edit_processing.php');
transfer('game_id');
transfer('gal_on');
$submit = [
	'value' => 'Edit Galaxies',
	'href' => SmrSession::getNewHREF($container),
];
$template->assign('Submit', $submit);

$galaxies = [];
foreach (SmrGalaxy::getGameGalaxies($var['game_id']) as $galaxy) {
	$galaxies[$galaxy->getGalaxyID()] = [
		'Name' => $galaxy->getName(),
		'Width' => $galaxy->getWidth(),
		'Height' => $galaxy->getHeight(),
		'Type' => $galaxy->getGalaxyType(),
		'ForceMaxHours' => $galaxy->getMaxForceTime() / 3600,
	];
}
$template->assign('Galaxies', $galaxies);

$container = create_container('skeleton.php', '1.6/universe_create_sectors.php');
transfer('game_id');
transfer('gal_on');
$template->assign('BackHREF', SmrSession::getNewHREF($container));
