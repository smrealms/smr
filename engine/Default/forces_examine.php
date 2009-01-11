<?

// creates a new player object for attacker and defender
$forces_owner	=& SmrPlayer::getPlayer($var['owner_id'], SmrSession::$game_id);
require_once(get_file_loc('SmrForce.class.inc'));
$forces =& SmrForce::getForce($player->getGameID(), $player->getSectorID(), $var['owner_id']);

// first check if both ship and forces are in same sector
if ($player->getSectorID() != $forces->getSectorID()) {

	create_error('Those forces are no longer here!');
	return;

}

$PHP_OUTPUT.=('<h1>EXAMINE FORCES</h1>');

// should we display an attack button
if (($ship->getAttackRating() > 0 || $ship->getCDs() > 0) &&
	!$player->hasFederalProtection() &&
	$player->getNewbieTurns() == 0 &&
	!$player->isLandedOnPlanet() &&
	($player->getAllianceID() == 0 || $forces_owner->getAllianceID() == 0 || $forces_owner->getAllianceID() != $player->getAllianceID()) &&
	$player->getAccountID() != $forces_owner->getAccountID()) {

	$container = array();
	$container['url'] = 'forces_attack_processing.php';
	transfer('target');
	transfer('owner_id');
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=create_submit('Attack Forces (3)');
	$PHP_OUTPUT.=('</form>');

} elseif ($player->hasFederalProtection())
	$PHP_OUTPUT.=('<p><big style="color:#3333FF;">You are under federal protection! That wouldn\'t be fair.</big></p>');
elseif ($player->getNewbieTurns() > 0)
	$PHP_OUTPUT.=('<p><big style="color:#33FF33;">You are under newbie protection!</big></p>');
elseif ($owner->getAllianceID() == $player->getAllianceID() && $player->getAllianceID() != 0)
	$PHP_OUTPUT.=('<p><big style="color:#33FF33;">These are your alliance\'s forces!</big></p>');
elseif ($owner->getAccountID() == $player->getAccountID())
	$PHP_OUTPUT.=('<p><big style="color:#33FF33;">These are your forces!</big></p>');

$PHP_OUTPUT.=('<div align="center">');
$PHP_OUTPUT.=('<table cellspacing="0" cellpadding="5" border="0" class="standard" width="95%">');
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

require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID(), SmrSession::$account_id);
$attackers =& $sector->getFightingTradersAgainstForces($player, $forces);

$PHP_OUTPUT.=('<td valign="top">');

foreach($attackers as &$attacker)
{
	 $attackerShip =& $attacker->getShip();

	 $PHP_OUTPUT.=($attacker->getLevelName().'<br />');
	 $PHP_OUTPUT.=('<span style="color:yellow;">'.$attacker->getPlayerName().' ('.$attacker->getPlayerID().')</span><br />');
	 $PHP_OUTPUT.=('Race: '.$attacker->getRaceName().'<br />');
	 $PHP_OUTPUT.=('Level: '.$attacker->getLevelID().'<br />');
	 $PHP_OUTPUT.=('Alliance: '.$attacker->getAllianceName().'<br /><br />');
	 $PHP_OUTPUT.=('<small>');
	 $PHP_OUTPUT.=($attackerShip->getName().'<br />');
	 $PHP_OUTPUT.=('Rating : ' . $attackerShip->getAttackRating() . '/' . $attackerShip->getDefenseRating() . '<br />');
	 $PHP_OUTPUT.=('Shields : ' . $attackerShip->shield_low() . '-' . $attackerShip->shield_high() . '<br />');
	 $PHP_OUTPUT.=('Armor : ' . $attackerShip->armor_low() . '-' . $attackerShip->armor_high() . '<br />');
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

if ($player->forceNAPAlliance($forces_owner)) {

	// you can't attack ur own alliance forces.

	$PHP_OUTPUT.=('&nbsp;</td>');
	$PHP_OUTPUT.=(' </tr>');
	$PHP_OUTPUT.=(' </table>');
	$PHP_OUTPUT.=('</div>');
	return;

}

$PHP_OUTPUT.=('Mines: '.$forces->getMines().'<br />');
$PHP_OUTPUT.=('Combat Drones: '.$forces->getCDs().'<br />');
$PHP_OUTPUT.=('Scouts: '.$forces->getSDs().'<br />');
$PHP_OUTPUT.=('Alliance: '.$forces_owner->getAllianceName().'<br /><br />');


$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('	 </tr>');
$PHP_OUTPUT.=('	 </table>');
$PHP_OUTPUT.=('</div>');

?>