<?php declare(strict_types=1);

$numGals = SmrSession::getRequestVarInt('num_gals', 12);

$game = SmrGame::getGame($var['game_id']);
$template->assign('PageTopic', 'Create Galaxies : ' . $game->getDisplayName());
$template->assign('GameEnabled', $game->isEnabled());

// Link for updating the number of galaxies
$container = $var;
$template->assign('UpdateNumGalsHREF', SmrSession::getNewHREF($container));

// Link for creating galaxies
$container['url'] = '1.6/universe_create_save_processing.php';
$container['body'] = '1.6/universe_create_sectors.php';
$submit = [
	'value' => 'Create Galaxies',
	'href' => SmrSession::getNewHREF($container),
];
$template->assign('Submit', $submit);

// Link for creating universe from SMR file
$container['url'] = '1.6/upload_smr_file_processing.php';
$template->assign('UploadSmrFileHREF', SmrSession::getNewHREF($container));

//Galaxy Creation area
$defaultNames = array(0, 'Alskant', 'Creonti', 'Human', 'Ik\'Thorne', 'Nijarin', 'Salvene', 'Thevian', 'WQ Human', 'Omar', 'Salzik', 'Manton', 'Livstar', 'Teryllia', 'Doriath', 'Anconus', 'Valheru', 'Sardine', 'Clacher', 'Tangeria');
$template->assign('NumGals', $numGals);

$galaxies = [];
for ($i = 1; $i <= $numGals; ++$i) {
	$isRacial = $i <= 8;
	$galaxies[$i] = [
		'Name' => $defaultNames[$i] ?? 'Unknown',
		'Width' => 10,
		'Height' => 10,
		'Type' => $isRacial ? 'Racial' : 'Neutral',
		'ForceMaxHours' => $isRacial ? 12 : 60,
	];
}
$template->assign('Galaxies', $galaxies);
