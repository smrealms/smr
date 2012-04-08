<?php

$template->assign('PageTopic','Create Universe - Create Game (1/10)');

// create a container that will hold next url and additional variables.
$container = array();
$container['url'] = 'universe_create_game_processing.php';

$template->assign('CreateUniverseFormHref',SmrSession::getNewHREF($container));

$db->query('SELECT * FROM game ORDER BY game_id');
$games = array();
while ($db->nextRecord())
	$games[] = array('ID'=>$db->getField('game_id'), 'Name' => $db->getField('game_name'));
$template->assign('Games',$games);
$template->assign('DefaultStartDate',date('Y/m/d',TIME));
$template->assign('DefaultEndDate',date('Y/m/d',TIME + 5184000));

$db->query('SELECT game_name
			FROM game
			WHERE enabled = \'FALSE\'');
if ($db->getNumRows())
{
	$disabledGames=array();
	while ($db->nextRecord())
		$disabledGames[] = $db->getField('game_name');
	$template->assign('DisabledGames',$disabledGames);

}

?>
