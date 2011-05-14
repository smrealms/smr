<?php
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');
require_once(get_file_loc('SmrPlanet.class.inc'));
$planet =& SmrPlanet::getPlanet($player->getGameID(),$player->getSectorID());
$action = $_REQUEST['action'];
if ($action == 'Build')
{
	if(($message = $planet->canBuild($player, $var['construction_id']))!==true)
	{
		create_error($message);
	}

	// now start the construction
	$planet->startBuilding($player,$var['construction_id']);
	$player->increaseHOF(1,array('Planet','Buildings','Started'), HOF_ALLIANCE);

	$PLANET_BUILDINGS = Globals::getPlanetBuildings();
	$account->log(11, 'Player starts a '.$PLANET_BUILDINGS[]['Name'].' on planet.', $player->getSectorID());

}
elseif ($action == 'Cancel')
{
	$planet->stopBuilding($var['construction_id']);
	$player->increaseHOF(1,array('Planet','Buildings','Stopped'), HOF_ALLIANCE);
	$account->log(11, 'Player cancels planet construction', $player->getSectorID());
}

forward(create_container('skeleton.php', 'planet_construction.php'));

?>