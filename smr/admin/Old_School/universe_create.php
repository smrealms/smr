<?

$smarty->assign('PageTopic','CREATE UNIVERSE - CREATE GAME (1/10)');

// create a container that will hold next url and additional variables.
$container = array();
$container['url'] = 'universe_create_game_processing.php';

$smarty->assign('CreateUniverseFormSN',SmrSession::get_new_sn($container));
$smarty->assign('CreateUniverseFormAction','loader.php');

$db->query('SELECT * FROM game ORDER BY game_id');
$games = array();
while ($db->next_record())
	$games[] = array('ID'=>$db->f('game_id'), 'Name' => $db->f('game_name'));
$smarty->assign('Games',$games);
$smarty->assign('DefaultStartDate',date('Y/m/d',TIME));
$smarty->assign('DefaultEndDate',date('Y/m/d',TIME + 5184000));

$db->query('SELECT game_name
			FROM game
			WHERE enabled = \'FALSE\'');
if ($db->nf())
{
	$disabledGames=array();
	while ($db->next_record())
		$disabledGames[] = $db->f('game_name');
	$smarty->assign('DisabledGames',$disabledGames);

}

?>
