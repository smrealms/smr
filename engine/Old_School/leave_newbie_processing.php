<?
$action = $_REQUEST['action'];
if ($action == 'Yes!')
{
	$player->setNewbieTurns(0);
	$player->setNewbieWarning(false);
}
if ($player->isLandedOnPlanet())
	$area = 'planet_main.php';
else
	$area = 'current_sector.php';
$account->log(5, 'Player drops newbie turns.', $player->getSectorID());
forward(create_container('skeleton.php', $area));

?>