<?

$smarty->assign('PageTopic','CREATE UNIVERSE - ADDING GALAXIES (2/10)');

$db->query('SELECT * FROM game WHERE game_id = ' . $var['game_id']);
if ($db->next_record())
	$smarty->assign('GameName',$db->f('game_name'));
$galaxy_count = isset($_REQUEST['galaxy_count']) ? $_REQUEST['galaxy_count'] : 0;
if (empty($galaxy_count)) {

	// do we already have galaxies?
	$db->query('SELECT * FROM sector WHERE game_id = ' . $var['game_id'] . ' GROUP BY galaxy_id');
	$galaxy_count = $db->nf();

}

if (empty($galaxy_count))
{

	$container = array();
	$container['url']		= 'skeleton.php';
	$container['body']		= 'universe_create_galaxies.php';
	$container['game_id']	= $var['game_id'];
	$smarty->assign('ChooseNumberOfGalaxies',true);
	$smarty->assign('CreateGalaxiesFormAction','loader.php');
	$smarty->assign('CreateGalaxiesFormSN',SmrSession::get_new_sn($container));
}
else
{
	$container = array();
	$container['url']			= 'universe_create_galaxies_processing.php';
	$container['game_id']		= $var['game_id'];
	$smarty->assign('CreateGalaxiesFormAction','loader.php');
	$smarty->assign('CreateGalaxiesFormSN',SmrSession::get_new_sn($container));

	$smarty->assign('NumberOfGalaxies',$galaxy_count);
	
	$db->query('SELECT * FROM galaxy');
	$galaxyNames = array();
	while ($db->next_record()) {

		$galaxyNames[$db->f('galaxy_id')] = $db->f('galaxy_name');
	}
	$smarty->assign('GalaxyNames',$galaxyNames);


	// do we already have galaxies?
	$db->query('SELECT * FROM sector WHERE game_id = ' . $var['game_id']);
	if ($db->nf() > 0)
		$smarty->assign('CanSkip', true);

}

?>