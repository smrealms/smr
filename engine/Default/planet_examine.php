<?php
// get a planet from the sector where the player is in
$planet =& $player->getSectorPlanet();
// owner of planet
if ($planet->hasOwner()) {
	$planet_owner =& $planet->getOwner();
	$ownerAllianceID = $planet_owner->getAllianceID();
} else $ownerAllianceID = 0;
$template->assign('PageTopic','Examine Planet');
$PHP_OUTPUT.=('<table>');
$PHP_OUTPUT.=('<tr><td><b>Planet Name:</b></td><td>'.$planet->getName().'</td></tr>');
$PHP_OUTPUT.=('<tr><td><b>Level:</b></td><td>' . number_format($planet->getLevel(),2) . '</td></tr>');
$PHP_OUTPUT.=('<tr><td><b>Owner:</b></td><td>');
if ($planet->hasOwner())
	$PHP_OUTPUT.=($planet_owner->getLinkedDisplayName(false));
else
	$PHP_OUTPUT.=('Unclaimed');

$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('<tr><td><b>Alliance:</b></td><td>');

if ($planet->hasOwner())
	$PHP_OUTPUT.=create_link($planet_owner->getAllianceRosterHREF(), $planet_owner->getAllianceName());
else
	$PHP_OUTPUT.=('none');

$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</table>');

$PHP_OUTPUT.=('<div align="center">');

// land or attack?
//check for treaty
$planetLand = FALSE;
$db->query('SELECT planet_land FROM alliance_treaties
				WHERE (alliance_id_1 = '.$ownerAllianceID.' OR alliance_id_1 = ' . $db->escapeNumber($player->getAllianceID()) . ')
				AND (alliance_id_2 = '.$ownerAllianceID.' OR alliance_id_2 = ' . $db->escapeNumber($player->getAllianceID()) . ')
				AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
				AND planet_land = 1 AND official = ' . $db->escapeBoolean(true));
if ($db->nextRecord()) $planetLand = TRUE;
if (in_array($player->getAccountID(), Globals::getHiddenPlayers())) $planetLand = TRUE;
if ($player->getAllianceID() == $ownerAllianceID && $ownerAllianceID != 0) $planetLand = TRUE;
if ($planet->getOwnerID() == $player->getAccountID()) $planetLand = TRUE;
if (!$planet->hasOwner()) $planetLand = TRUE;
if (!$planetLand)
	$PHP_OUTPUT.=create_button(create_container('planet_attack_processing.php', ''), 'Attack Planet (3)');
elseif ($planet->isInhabitable())
	$PHP_OUTPUT.=create_button(create_container('planet_land_processing.php', ''), 'Land on Planet (1)');
else
	$PHP_OUTPUT.=('The planet is <span class"uninhab">uninhabitable</span> at this time.');
$PHP_OUTPUT.=('</form>');
$PHP_OUTPUT.=('</div>');

?>