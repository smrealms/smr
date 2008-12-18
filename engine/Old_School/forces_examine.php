<?
// initialize random generator.
mt_srand((double)microtime()*1000000);

// creates a new player object for attacker and defender
$attacker		=& SmrPlayer::getPlayer(SmrSession::$account_id, SmrSession::$game_id);
$attacker_ship	=& SmrShip::getShip(SmrSession::$game_id,SmrSession::$account_id);
$forces_owner	=& SmrPlayer::getPlayer($var['owner_id'], SmrSession::$game_id);
require_once(get_file_loc('SmrForce.class.inc'));
$forces =& SmrForce::getForce($player->getGameID(), $player->getSectorID(), $var['owner_id']);

// first check if both ship and forces are in same sector
if ($attacker->getSectorID() != $forces->getSectorID()) {

	$PHP_OUTPUT.=create_echo_error('Those forces are no longer here!');
	return;

}

$PHP_OUTPUT.=('<h1>EXAMINE FORCES</h1>');

// should we display an attack button
if (($attacker_ship->attack_rating() > 0 || $attacker_ship->getCDs() > 0) &&
	!$attacker->isFedProtected() &&
	$attacker->getNewbieTurns() == 0 &&
	!$attacker->isLandedOnPlanet() &&
	($attacker->getAllianceID() == 0 || $forces_owner->getAllianceID() == 0 || $forces_owner->getAllianceID() != $attacker->getAllianceID()) &&
	$attacker->getAccountID() != $forces_owner->getAccountID()) {

	$container = array();
	$container['url'] = 'forces_attack_processing.php';
	transfer('target');
	transfer('owner_id');
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=create_submit('Attack Forces (3)');
	$PHP_OUTPUT.=('</form>');

} elseif ($attacker->isFedProtected())
	$PHP_OUTPUT.=('<p><big style="color:#3333FF;">You are under federal protection! That wouldn\'t be fair.</big></p>');
elseif ($attacker->getNewbieTurns() > 0)
	$PHP_OUTPUT.=('<p><big style="color:#33FF33;">You are under newbie protection!</big></p>');
elseif ($owner->getAllianceID() == $attacker->getAllianceID() && $attacker->getAllianceID() != 0)
	$PHP_OUTPUT.=('<p><big style="color:#33FF33;">These are your alliance\'s forces!</big></p>');
elseif ($owner->getAccountID() == $attacker->getAccountID())
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

if ($attacker->getAccountID() > 0) {

	$db->query('SELECT * FROM player WHERE game_id = '.SmrSession::$game_id.' AND ' .
													  'alliance_id = '.$attacker->getAccountID().' AND ' .
													  'sector_id = '.$attacker->getSectorID().' AND ' .
													  'land_on_planet = \'FALSE\' AND ' .
													  'newbie_turns = 0');

	while ($db->next_record()) {

		$curr_player =& SmrPlayer::getPlayer($db->f('account_id'), SmrSession::$game_id);

		if (!$curr_player->isFedProtected()) {

			if ($attacker_list) $attacker_list .= ',';
			$attacker_list .= $curr_player->getAccountID();

		}

	}

	$attacker_list = '(' . $attacker_list . ')';

} else {

	// mhh. we are not in an alliance.
	// so we fighting alone.
	$attacker_list = '(' . $attacker->account_id . ')';

}

$PHP_OUTPUT.=('<td valign="top">');

if ($attacker_list == '()') {

	$PHP_OUTPUT.=('&nbsp;');

} else {

	$db->query('SELECT * FROM player WHERE game_id = '.SmrSession::$game_id.' AND ' .
													  'account_id IN '.$attacker_list);
	while ($db->next_record()) {

		 $curr_player =& SmrPlayer::getPlayer($db->f('account_id'), SmrSession::$game_id);
		 $curr_ship =& SmrShip::getShip(SmrSession::$game_id,$db->f('account_id'));

		 $PHP_OUTPUT.=($player->getLevelName().'<br>');
		 $PHP_OUTPUT.=('<span style="color:yellow;">'.$curr_player->getPlayerName().' ('.$curr_player->getPlayerID().')</span><br>');
		 $PHP_OUTPUT.=('Race: '.$curr_player->getRaceName().'<br>');
		 $PHP_OUTPUT.=('Level: '.$curr_player->level_id.'<br>');
		 $PHP_OUTPUT.=('Alliance: '.$curr_player->getAllianceName().'<br><br>');
		 $PHP_OUTPUT.=('<small>');
		 $PHP_OUTPUT.=($curr_ship->ship_name.'<br>');
		 $PHP_OUTPUT.=('Rating : ' . $curr_ship->attack_rating() . '/' . $curr_ship->defense_rating() . '<br>');
		 $PHP_OUTPUT.=('Shields : ' . $curr_ship->shield_low() . '-' . $curr_ship->shield_high() . '<br>');
		 $PHP_OUTPUT.=('Armor : ' . $curr_ship->armor_low() . '-' . $curr_ship->armor_high() . '<br>');
		 $PHP_OUTPUT.=('Hard Points: '.$curr_ship->weapon_used.'<br>');
		 $PHP_OUTPUT.=('Combat Drones: ' . $curr_ship->combat_drones_low() . '-' . $curr_ship->combat_drones_high());
		 $PHP_OUTPUT.=('</small><br><br><br>');

	}

}

$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('<td valign="top">');

// ********************************
// *
// * F o r c e s
// *
// ********************************

if ($attacker->forceNAPAlliance($forces_owner)) {

	// you can't attack ur own alliance forces.

	$PHP_OUTPUT.=('&nbsp;</td>');
	$PHP_OUTPUT.=(' </tr>');
	$PHP_OUTPUT.=(' </table>');
	$PHP_OUTPUT.=('</div>');
	return;

}

$PHP_OUTPUT.=('Mines: '.$forces->getMines().'<br>');
$PHP_OUTPUT.=('Combat Drones: '.$forces->getCDs().'<br>');
$PHP_OUTPUT.=('Scouts: '.$forces->getSDs().'<br>');
$PHP_OUTPUT.=('Alliance: '.$forces_owner->getAllianceName().'<br><br>');


$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('	 </tr>');
$PHP_OUTPUT.=('	 </table>');
$PHP_OUTPUT.=('</div>');

?>