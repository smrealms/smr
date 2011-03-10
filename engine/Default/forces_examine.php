<?php

// creates a new player object for attacker and defender
require_once(get_file_loc('SmrForce.class.inc'));
$forces =& SmrForce::getForce($player->getGameID(), $player->getSectorID(), $var['owner_id']);

// first check if both ship and forces are in same sector
if ($player->getSectorID() != $forces->getSectorID())
{
	create_error('Those forces are no longer here!');
}

$forces_owner =& $forces->getOwner();

$template->assign('PageTopic','Examine Forces');

// should we display an attack button
if (($ship->getAttackRating() > 0 || $ship->getCDs() > 0) &&
	!$player->hasFederalProtection() &&
	!$player->hasNewbieTurns() &&
	!$player->isLandedOnPlanet() &&
	!$player->forceNAPAlliance($forces_owner)) {

	$container = array();
	$container['url'] = 'forces_attack_processing.php';
	transfer('target');
	transfer('owner_id');
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=create_submit('Attack Forces (3)');
	$PHP_OUTPUT.=('</form><br />');

} elseif ($player->hasFederalProtection())
	$PHP_OUTPUT.=('<p><big style="color:#3333FF;">You are under federal protection! That wouldn\'t be fair.</big></p>');
elseif ($player->hasNewbieTurns())
	$PHP_OUTPUT.=('<p><big style="color:#33FF33;">You are under newbie protection!</big></p>');
elseif ($forces_owner->getAccountID() == $player->getAccountID())
	$PHP_OUTPUT.=('<p><big style="color:#33FF33;">These are your forces!</big></p>');
elseif ($player->forceNAPAlliance($forces_owner))
	$PHP_OUTPUT.=('<p><big style="color:#33FF33;">These are allied forces!</big></p>');

$PHP_OUTPUT.=('<div align="center">');
$PHP_OUTPUT.=('<table class="standard" width="95%">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th width="50%">Attacker</th>');
$PHP_OUTPUT.=('<th width="50%">Forces</th>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr>');

// ********************************
// *
// * A t t a c k e r
// *
// ********************************

$sector =& $player->getSector();
$attackers =& $sector->getFightingTradersAgainstForces($player, $forces);

$PHP_OUTPUT.=('<td valign="top">');

foreach($attackers as &$attacker)
{
	 $attackerShip =& $attacker->getShip();

	 $PHP_OUTPUT.=($attacker->getLevelName().'<br />');
	 $PHP_OUTPUT.=($attacker->getLinkedDisplayName(false).'<br />');
	 $PHP_OUTPUT.=('Race: '.$attacker->getRaceName().'<br />');
	 $PHP_OUTPUT.=('Level: '.$attacker->getLevelID().'<br />');
	 $PHP_OUTPUT.=('Alliance: '.create_link($attacker->getAllianceRosterHREF(), $attacker->getAllianceName()).'<br /><br />');
	 $PHP_OUTPUT.=('<small>');
	 $PHP_OUTPUT.=($attackerShip->getName().'<br />');
	 $PHP_OUTPUT.=('Rating : ' . $attackerShip->getAttackRating() . '/' . $attackerShip->getDefenseRating() . '<br />');
	 $PHP_OUTPUT.=('Shields : ' . $attackerShip->shield_low() . '-' . $attackerShip->shield_high() . '<br />');
	 $PHP_OUTPUT.=('Armour : ' . $attackerShip->armour_low() . '-' . $attackerShip->armour_high() . '<br />');
	 $PHP_OUTPUT.=('Hard Points: '.$attackerShip->getNumWeapons().'<br />');
	 $PHP_OUTPUT.=('Combat Drones: ' . $attackerShip->combat_drones_low() . '-' . $attackerShip->combat_drones_high());
	 $PHP_OUTPUT.=('</small><br /><br /><br />');
}

$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('<td valign="top">');

// ********************************
// *
// * F o r c e s
// *
// ********************************

if ($player->forceNAPAlliance($forces_owner))
{
	// you can't attack ur own alliance forces.
	$PHP_OUTPUT.='&nbsp;';
}
else
{
	$PHP_OUTPUT.=('Mines: '.$forces->getMines().'<br />');
	$PHP_OUTPUT.=('Combat Drones: '.$forces->getCDs().'<br />');
	$PHP_OUTPUT.=('Scouts: '.$forces->getSDs().'<br /><br />');
}

$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</div>');

?>