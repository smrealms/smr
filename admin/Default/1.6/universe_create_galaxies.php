<?php

$numGals = SmrSession::getRequestVar('num_gals', 12);

// Link for updating the number of galaxies
$container = $var;
$template->assign('UpdateNumGalsHREF', SmrSession::getNewHREF($container));

// Link for creating galaxies
$container['url'] = '1.6/universe_create_save_processing.php';
$container['body'] = '1.6/universe_create_sectors.php';
$template->assign('CreateGalaxiesHREF', SmrSession::getNewHREF($container));

// Link for creating universe from SMR file
$container['url'] = '1.6/upload_smr_file_processing.php';
$template->assign('UploadSmrFileHREF', SmrSession::getNewHREF($container));

//Galaxy Creation area
$defaultNames = array(0,'Alskant','Creonti','Human','Ik\'Thorne','Nijarin','Salvene','Thevian','WQ Human','Omar','Salzik','Manton','Livstar','Teryllia','Doriath','Anconus','Valheru','Sardine','Clacher','Tangeria');
$template->assign('DefaultNames', $defaultNames);
$template->assign('NumGals', $numGals);
$template->assign('GalaxyTypes', array('Racial','Neutral','Planet'));
