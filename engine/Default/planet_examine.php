<?php
require_once(get_file_loc('SmrPlanet.class.inc'));
// get a planet from the sector where the player is in
$planet =& SmrPlanet::getPlanet($player->getGameID(),$player->getSectorID());
// owner of planet
if ($planet->hasOwner())
{
	$planet_owner =& $planet->getOwner();
	$ownerAllianceID = $planet_owner->getAllianceID();
} else $ownerAllianceID = 0;
$template->assign('PageTopic','Examine Planet');
$PHP_OUTPUT.=('<table>');
$PHP_OUTPUT.=('<tr><td><b>Planet Name:</b></td><td>'.$planet->planet_name.'</td></tr>');
$PHP_OUTPUT.=('<tr><td><b>Level:</b></td><td>' . number_format($planet->getLevel(),2) . '</td></tr>');
$PHP_OUTPUT.=('<tr><td><b>Owner:</b></td><td>');
if ($planet->owner_id != 0)
	$PHP_OUTPUT.=($planet_owner->getPlayerName());
else
	$PHP_OUTPUT.=('Unclaimed');

$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('<tr><td><b>Alliance:</b></td><td>');

if ($planet->owner_id != 0)
	$PHP_OUTPUT.=($planet_owner->getAllianceName());
else
	$PHP_OUTPUT.=('none');

$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</table>');

$PHP_OUTPUT.=('<div align="center">');

// land or attack?
//check for treaty
$planetLand = FALSE;
$db->query('SELECT planet_land FROM alliance_treaties
				WHERE (alliance_id_1 = '.$ownerAllianceID.' OR alliance_id_1 = '.$player->getAllianceID().')
				AND (alliance_id_2 = '.$ownerAllianceID.' OR alliance_id_2 = '.$player->getAllianceID().')
				AND game_id = '.$player->getGameID().'
				AND planet_land = 1 AND official = \'TRUE\'');
if ($db->nextRecord()) $planetLand = TRUE;
if (in_array($player->getAccountID(), Globals::getHiddenPlayers())) $planetLand = TRUE;
if ($player->getAllianceID() == $ownerAllianceID && $ownerAllianceID != 0) $planetLand = TRUE;
if ($planet->owner_id == $player->getAccountID()) $planetLand = TRUE;
if ($planet->owner_id == 0) $planetLand = TRUE;
if (!$planetLand)
	$PHP_OUTPUT.=create_button(create_container('planet_attack_processing.php', ''), 'Attack Planet (3)');
elseif ($planet->isInhabitable())
	$PHP_OUTPUT.=create_button(create_container('planet_land_processing.php', ''), 'Land on Planet (1)');
else
	$PHP_OUTPUT.=('The planet is <span class"uninhab">uninhabitable</span> at this time.');
$PHP_OUTPUT.=('</form>');
$PHP_OUTPUT.=('</div>');

?>