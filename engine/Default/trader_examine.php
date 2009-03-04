<?

// Get the player we're attacking
$targetPlayer =& SmrPlayer::getPlayer($var['target'],SmrSession::$game_id);

if($targetPlayer->isDead())
{
	$container=array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'current_sector.php';
	$container['msg'] = '<span class="red bold">ERROR:</span> Target already dead.';
	forward($container);
}


$template->assign('PageTopic','EXAMINE SHIP');
// should we display a attack button
if ($ship->canAttack() && !$player->hasFederalProtection() && !$targetPlayer->hasFederalProtection() && !$player->hasNewbieTurns() && !$targetPlayer->hasNewbieTurns() && !$player->sameAlliance($targetPlayer) && !$player->traderNAPAlliance($targetPlayer))
{
	$canAttack=true;
	$container = create_container('skeleton.php','trader_attack_processing.php');
	transfer('target');
	$container['time'] = microtime(true);
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=create_submit('Attack Trader (3)');
	$PHP_OUTPUT.=('</form><br />');
}
else
{
	$canAttack=false;
	$PHP_OUTPUT.= '<p><big class="';
	if ($player->sameAlliance($targetPlayer)) $PHP_OUTPUT.= 'blue">This is your alliancemate.';
	else if ($player->traderNAPAlliance($targetPlayer)) $PHP_OUTPUT.= 'blue">This is your ally.';
	else if ($player->hasFederalProtection()) $PHP_OUTPUT.= 'blue">You are under federal protection! That wouldn\'t be fair.';
	else if ($targetPlayer->hasFederalProtection()) $PHP_OUTPUT.= 'blue">Your target is under federal protection!';
	else if ($player->hasNewbieTurns()) $PHP_OUTPUT.= 'green">You are under newbie protection!';
	else if ($targetPlayer->hasNewbieTurns()) $PHP_OUTPUT.= 'green">Your target is under newbie protection!';
	else if (!$ship->canAttack()) $PHP_OUTPUT.= 'red">You ready your weapons, you take aim, you...realize you have no weapons.';
	else $PHP_OUTPUT.= 'red">Uhhhh, something is wrong.  Screenshot and tell Page please.';
	$PHP_OUTPUT.= '</big></p>';
}
if($canAttack)
	$fightingPlayers =& $sector->getFightingTraders($player,$targetPlayer);
else
	$fightingPlayers =& $sector->getPotentialFightingTraders($player);
$fightingPlayers['Attackers'][$player->getAccountID()] =& $player;
$PHP_OUTPUT.= '<div align="center">';
$PHP_OUTPUT.= '<table cellspacing="0" cellpadding="5" border="0" class="standard" width="95%">';
$PHP_OUTPUT.= '<tr><th width="50%">Attacker</th><th width="50%">Defender</th></tr>';
$PHP_OUTPUT.=('<tr>');
foreach ($fightingPlayers as $fleet)
{
	$PHP_OUTPUT.= '<td style="vertical-align:top;">';
	if (is_array($fleet))
		foreach ($fleet as &$fleetPlayer)
		{
			$fleetShip =& $fleetPlayer->getShip();
			if (isset($customTags[$accID])) $PHP_OUTPUT.=($customTags[$accID]);
			else $PHP_OUTPUT.=($fleetPlayer->getLevelName());
			$PHP_OUTPUT.=('<br />');
			$PHP_OUTPUT.=($fleetPlayer->getDisplayName() . '<br />');
			$PHP_OUTPUT.=('Race: ' . $fleetPlayer->getRaceName() . '<br .>');
			$PHP_OUTPUT.=('Level: ' . $fleetPlayer->getLevelName() . '<br />');
			$PHP_OUTPUT.=('Alliance: ' . $fleetPlayer->getAllianceName() . '<br /><br />');
			$PHP_OUTPUT.=('<small>' . $fleetShip->getName() . '<br />');
			$PHP_OUTPUT.=('Rating : ' . $fleetShip->getDisplayAttackRating($player) . '/' . $fleetShip->getDisplayDefenseRating($player) . '<br />');
			if ($ship->hasScanner())
			{
				$PHP_OUTPUT.=('Shields : ' . $fleetShip->shield_low() . '-' . $fleetShip->shield_high() . '<br />');
				$PHP_OUTPUT.=('Armour : ' . $fleetShip->armour_low() . '-' . $fleetShip->armour_high() . '<br />');
				$PHP_OUTPUT.=('Hard Points: ' . $fleetShip->getNumWeapons() . '<br />');
				$PHP_OUTPUT.=('Combat Drones: ' . $fleetShip->combat_drones_low() . '-' . $fleetShip->combat_drones_high() . '<br />');
			}
			$PHP_OUTPUT.=('</small><br /><br />');
		}
	else
		$PHP_OUTPUT.=('&nbsp;');
	$PHP_OUTPUT.=('</td>');
}
if(!$canAttack)
		$PHP_OUTPUT.=('<td>&nbsp;</td>');
$PHP_OUTPUT.=('</tr></table></div>');
?>