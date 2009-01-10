<?
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');
require_once(get_file_loc('SmrPlanet.class.inc'));
$planet =& SmrPlanet::getPlanet($player->getGameID(),$player->getSectorID());
$action = $_REQUEST['action'];
if ($action == 'Build') {

	if ($planet->build())
		create_error('There is already a building in progress!');

	$player->decreaseCredits($var['cost']);
	$player->update();

	// now start the construction
	$planet->startBuilding($player->getAccountID(),$var['construction_id']);

	$db->query('SELECT * FROM planet_construction WHERE construction_id = ' . $var['construction_id']);
	$db->next_record();
	$name = $db->f('construction_name');
	$account->log(11, 'Player starts a '.$name.' on planet.', $player->getSectorID());

} elseif ($action == 'Cancel') {

	$db->query('DELETE FROM planet_is_building WHERE sector_id = '.$player->getSectorID().' AND game_id = '.$player->getGameID());
	$account->log(11, 'Player cancels planet construction', $player->getSectorID());

}

forward(create_container('skeleton.php', 'planet_construction.php'));

?>