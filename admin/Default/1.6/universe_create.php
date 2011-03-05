<?php

$defaultEnd = TIME + (2*31*86400); //3 months 
$template->assign('DefaultEnd',$defaultEnd);

$template->assign('GameTypes',array('Default','Semi Wars','Draft'));

//get information
$container=create_container('1.6/universe_create_save_processing.php','1.6/universe_create_galaxies.php');
$template->assign('CreateGalaxiesHREF',SmrSession::get_new_href($container));

$container['body'] = '1.6/universe_create_sectors.php';
$template->assign('EditGameHREF',SmrSession::get_new_href($container));

$editGames=array();
$db->query('SELECT * FROM game'.($account->hasPermission(PERMISSION_EDIT_STARTED_GAMES)?'':' WHERE start_date > '. TIME).' ORDER BY game_id DESC');
while ($db->nextRecord())
{
	$editGames[$db->getField('game_id')] = array('GameID'=>$db->getField('game_id'),'GameName'=>$db->getField('game_name'));
}

$template->assignByRef('EditGames',$editGames);
?>