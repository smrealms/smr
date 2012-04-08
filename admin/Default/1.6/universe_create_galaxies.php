<?php
//if (isset($_POST['Create_Game']))
//{
	//universe_create_galaxies.php
	$container = create_container('1.6/universe_create_save_processing.php', '1.6/universe_create_sectors.php');
	$container['num_gals'] = $var['num_gals'];
	$container['game_id'] = $var['game_id'];
	
	$template->assign('CreateGalaxiesHREF',SmrSession::get_new_href($container));
	
	//Galaxy Creation area
	$defaultNames = array(0,'Alskant','Creonti','Human','Ik\'Thorne','Nijarin','Salvene','Thevian','WQ Human','Omar','Salzik','Manton','Livstar','Teryllia','Doriath','Anconus','Valheru','Sardine','Clacher','Tangeria');
	$template->assign('DefaultNames',$defaultNames);
	$template->assign('NumGals',$var['num_gals']);
	$template->assign('GalaxyTypes',array('Racial','Neutral','Planet'));
//}
//else
//{
//	$db->query('SELECT game_id FROM game ORDER BY game_id DESC LIMIT 1');
//	if ($db->nextRecord())
//	{
//		$var['game_id'] = $db->getField('game_id');
//		include('universe_create_sectors.php');
//	}
//	else
//	{
//		$error = '<span class="red">Error</span>: Could not find a previous game to edit.';
//		include($GAME_FILES . '/error.php');
//	}
//}

?>