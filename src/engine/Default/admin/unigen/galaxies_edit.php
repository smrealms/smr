<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$var = Smr\Session::getInstance()->getCurrentVar();

$game = SmrGame::getGame($var['game_id']);
$template->assign('PageTopic', 'Edit Galaxies : ' . $game->getDisplayName());
$template->assign('GameEnabled', $game->isEnabled());

$container = Page::create('admin/unigen/galaxies_edit_processing.php');
$container->addVar('game_id');
$container->addVar('gal_on');
$submit = [
	'value' => 'Edit Galaxies',
	'href' => $container->href(),
];
$template->assign('Submit', $submit);

$galaxies = [];
foreach ($game->getGalaxies() as $galaxy) {
	$galaxies[$galaxy->getGalaxyID()] = [
		'Name' => $galaxy->getDisplayName(),
		'Width' => $galaxy->getWidth(),
		'Height' => $galaxy->getHeight(),
		'Type' => $galaxy->getGalaxyType(),
		'ForceMaxHours' => $galaxy->getMaxForceTime() / 3600,
	];
}
$template->assign('Galaxies', $galaxies);

$container = Page::create('skeleton.php', 'admin/unigen/universe_create_sectors.php');
$container->addVar('game_id');
$container->addVar('gal_on');
$template->assign('BackHREF', $container->href());
