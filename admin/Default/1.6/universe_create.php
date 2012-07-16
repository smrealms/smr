<?php

$defaultEnd = TIME + (2*31*86400); //3 months 
$template->assign('DefaultEnd',$defaultEnd);

$template->assign('GameTypes',array('Default','Semi Wars','Draft','Newbie'));

//get information
$container=create_container('1.6/universe_create_save_processing.php','1.6/universe_create_galaxies.php');
$template->assign('CreateGalaxiesHREF',SmrSession::getNewHREF($container));

$container['body'] = '1.6/universe_create_sectors.php';
$template->assign('EditGameHREF',SmrSession::getNewHREF($container));

if($account->hasPermission(PERMISSION_EDIT_STARTED_GAMES)) {
	$games = Globals::getGameInfo();
}
else {
	$games = array();
	$db->query('SELECT game_id FROM game WHERE start_date > ' . $db->escapeNumber(TIME) . ' ORDER BY end_date DESC');
	while ($db->nextRecord()) {
		$games[$db->getInt('game_id')] = Globals::getGameInfo($db->getInt('game_id'));
	}
}
$template->assignByRef('EditGames',$games);
?>