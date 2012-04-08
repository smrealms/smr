<?php

$template->assign('PageTopic','Create Universe - Adding Galaxies (2/10)');

$db->query('SELECT * FROM game WHERE game_id = ' . $db->escapeNumber($var['game_id']));
if ($db->nextRecord())
	$template->assign('GameName',$db->getField('game_name'));
$galaxy_count = isset($_REQUEST['galaxy_count']) ? $_REQUEST['galaxy_count'] : 0;
if (empty($galaxy_count)) {

	// do we already have galaxies?
	$db->query('SELECT * FROM sector WHERE game_id = ' . $db->escapeNumber($var['game_id']) . ' GROUP BY galaxy_id');
	$galaxy_count = $db->getNumRows();

}

if (empty($galaxy_count))
{

	$container = array();
	$container['url']		= 'skeleton.php';
	$container['body']		= 'universe_create_galaxies.php';
	$container['game_id']	= $var['game_id'];
	$template->assign('ChooseNumberOfGalaxies',true);
	$template->assign('CreateGalaxiesFormHref',SmrSession::get_new_href($container));
}
else
{
	$container = array();
	$container['url']			= 'universe_create_galaxies_processing.php';
	$container['game_id']		= $var['game_id'];
	$template->assign('CreateGalaxiesFormHref',SmrSession::get_new_href($container));

	$template->assign('NumberOfGalaxies',$galaxy_count);
	
	$db->query('SELECT * FROM galaxy');
	$galaxyNames = array();
	while ($db->nextRecord()) {

		$galaxyNames[$db->getField('galaxy_id')] = $db->getField('galaxy_name');
	}
	$template->assign('GalaxyNames',$galaxyNames);


	// do we already have galaxies?
	$db->query('SELECT * FROM sector WHERE game_id = ' . $db->escapeNumber($var['game_id']));
	if ($db->getNumRows() > 0)
		$template->assign('CanSkip', true);

}

?>