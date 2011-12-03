<?php
$sector =& $player->getSector();
if(!$sector->isLinked($var['target_sector']) && $sector->getSectorID() != $var['target_sector'])
{
	create_error('You cannot scan a sector you are not linked to.');
}

// initialize vars
$scanSector =& SmrSector::getSector($player->getGameID(), $var['target_sector']);

$template->assign('PageTopic','Sector Scan of #'.$scanSector->getSectorID().' ('.$scanSector->getGalaxyName().')');

$friendly_forces = 0;
$enemy_forces = 0;
$friendly_vessel = 0;
$enemy_vessel = 0;

// iterate over all forces in the target sector
$scanSectorAllForces =& $scanSector->getForces();
foreach ($scanSectorAllForces as &$scanSectorForces)
{
	// decide if it's a friendly or enemy stack
	if ($player->sameAlliance($scanSectorForces->getOwner()))
		$friendly_forces += $scanSectorForces->getMines() * 3 + $scanSectorForces->getCDs() * 2 + $scanSectorForces->getSDs();
	else
		$enemy_forces += $scanSectorForces->getMines() * 3 + $scanSectorForces->getCDs() * 2 + $scanSectorForces->getSDs();
} unset($scanSectorForces);

$scanSectorPlayers =& $scanSector->getOtherTraders($player);
foreach($scanSectorPlayers as &$scanSectorPlayer)
{
	$scanSectorShip =& $scanSectorPlayer->getShip();

	// he's a friend if he's in our alliance (and we are not in a 0 alliance
	if ($player->traderMAPAlliance($scanSectorPlayer))
		$friendly_vessel += $scanSectorShip->getAttackRating();
	else
		$enemy_vessel += $scanSectorShip->getDefenseRating() * 10;
} unset($scanSectorPlayer);

$PHP_OUTPUT.=('<p>');
$PHP_OUTPUT.=('<table class="standard">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>&nbsp;</th>');
$PHP_OUTPUT.=('<th align="center">Scan Results</th>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Friendly vessels</td>');
$PHP_OUTPUT.=('<td align="center">'.$friendly_vessel.'</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Enemy vessels</td>');
$PHP_OUTPUT.=('<td align="center">'.$enemy_vessel.'</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Friendly forces</td>');
$PHP_OUTPUT.=('<td align="center">'.$friendly_forces.'</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Enemy forces</td>');
$PHP_OUTPUT.=('<td align="center">'.$enemy_forces.'</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</p>');

$PHP_OUTPUT.=('<p>');
$PHP_OUTPUT.=('<table class="standard">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Planet</td>');
$PHP_OUTPUT.=('<td>');
if ($scanSector->hasPlanet()) $PHP_OUTPUT.=('Yes'); else $PHP_OUTPUT.=('No');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Port</td>');
$PHP_OUTPUT.=('<td>');
if ($scanSector->hasPort()) $PHP_OUTPUT.=('Yes'); else $PHP_OUTPUT.=('No');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Location</td>');
$PHP_OUTPUT.=('<td>');
if ($scanSector->hasLocation()) $PHP_OUTPUT.=('Yes'); else $PHP_OUTPUT.=('No');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</p><br />');

// is it a warp or a normal move?
if ($sector->getWarp() == $var['target_sector'])
	$turns = TURNS_PER_WARP;
else
	$turns = TURNS_PER_SECTOR;

$PHP_OUTPUT.= '<a href="'.$scanSector->getScanSectorHREF().'" class="submitStyle">Rescan ' . $scanSector->getSectorID() . '</a>&nbsp;';
$PHP_OUTPUT.= '<a href="'.$scanSector->getCurrentSectorHREF().'" class="submitStyle">Enter ' . $scanSector->getSectorID() . ' ('.$turns.')</a>';
$PHP_OUTPUT.=('</form></p>');

?>