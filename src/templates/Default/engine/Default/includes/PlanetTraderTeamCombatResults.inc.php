<?php declare(strict_types=1);

use Smr\PlanetStructureType;

/**
 * @var Smr\Player $ThisPlayer
 * @var Smr\Template $this
 * @var bool $MinimalDisplay
 * @var PlanetAttackerCombatResults $TraderTeamCombatResults
 */

$AllTraderResults = $TraderTeamCombatResults['Traders'];
foreach ($AllTraderResults as $TraderResults) {
	$ShootingPlayer = $TraderResults['Player'];
	$TotalDamage = $TraderResults['TotalDamage'];
	if ($MinimalDisplay && !$ThisPlayer->equals($ShootingPlayer)) {
		echo $ShootingPlayer->getDisplayName();
		if ($TotalDamage > 0) {
			?> hits for a total of <span class="red"><?php echo $TotalDamage ?></span> damage in this round of combat<?php
		} else {
			?> does no damage this round<?php
		} ?>.<br /><br /><?php
		continue;
	}

	if ($TraderResults['DeadBeforeShot']) {
		echo $ShootingPlayer->getDisplayName() ?> died before they were able to attack!<br /><?php
	} else {
		foreach ($TraderResults['Weapons'] as $WeaponResults) {
			$ShootingWeapon = $WeaponResults['Weapon'];
			$ShotHit = $WeaponResults['Hit'];
			if ($ShotHit) {
				if (!isset($WeaponResults['ActualDamage']) || !isset($WeaponResults['WeaponDamage'])) {
					throw new Exception('Weapon hit without providing damage!');
				}
				$ActualDamage = $WeaponResults['ActualDamage'];
				$WeaponDamage = $WeaponResults['WeaponDamage'];
			}
			$TargetPlanet = $WeaponResults['Target'];

			echo $ShootingPlayer->getDisplayName() ?> fires their <?php echo $ShootingWeapon->getName() ?> at <?php if ($ShotHit && $ActualDamage['TargetAlreadyDead']) { ?>the debris that was once <?php } echo $TargetPlanet->getCombatName();
			if (!$ShotHit || !$ActualDamage['TargetAlreadyDead']) {
				if (!$ShotHit) {
					?> and misses<?php
				} elseif ($ActualDamage['TotalDamage'] === 0) {
					if ($WeaponDamage['Shield'] > 0) {
						if ($ActualDamage['HasCDs']) {
							?> which proves ineffective against their combat drones<?php
						} else {
							?> which proves ineffective against its armour<?php
						}
					} elseif ($WeaponDamage['Armour'] > 0) {
						?> which is deflected by their shields<?php
					} else {
						?> but it cannot do any damage<?php
					}
				} else {
					?> destroying <?php echo $this->displayTakenDamage($ActualDamage);
				}
			} ?>.
			<br /><?php
			if ($ShotHit && $ActualDamage['KillingShot']) {
				if (!isset($WeaponResults['KillResults'])) {
					throw new Exception('KillingShot did not provide KillResults!');
				}
				$this->includeTemplate('includes/PlanetKillMessage.inc.php', ['KillResults' => $WeaponResults['KillResults'], 'TargetPlanet' => $TargetPlanet]);
			}
		}
		if (isset($TraderResults['Drones'])) {
			$Drones = $TraderResults['Drones'];
			$ActualDamage = $Drones['ActualDamage'];
			$WeaponDamage = $Drones['WeaponDamage'];
			$TargetPlanet = $Drones['Target'];

			echo $ShootingPlayer->getDisplayName();
			if (!isset($WeaponDamage['Launched'])) {
				throw new Exception('Drone weapons must specify Launched');
			}
			if ($WeaponDamage['Launched'] === 0) {
				?> fails to launch their combat drones<?php
			} else {
				?> launches <span class="cds"><?php echo $WeaponDamage['Launched'] ?></span> combat drones at <?php
				if ($ActualDamage['TargetAlreadyDead']) {
					?>the debris that was once <?php
				}
				echo $TargetPlanet->getCombatName();
				if (!$ActualDamage['TargetAlreadyDead']) {
					if ($ActualDamage['TotalDamage'] === 0) {
						if ($WeaponDamage['Shield'] > 0) {
							if ($ActualDamage['HasCDs']) {
								?> which prove ineffective against their combat drones<?php
							} else {
								?> which prove ineffective against its armour<?php
							}
						}
						if ($ActualDamage['Armour'] > 0) {
							?> which is deflected by their shields<?php
						} else {
							?> but they cannot do any damage<?php
						}
					} else {
						?> destroying <?php echo $this->displayTakenDamage($ActualDamage);
					}
				}
			} ?>.
			<br /><?php
			if ($ActualDamage['KillingShot']) {
				if (!isset($Drones['KillResults'])) {
					throw new Exception('KillingShot did not provide KillResults!');
				}
				$this->includeTemplate('includes/PlanetKillMessage.inc.php', ['KillResults' => $Drones['KillResults'], 'TargetPlanet' => $TargetPlanet]);
			}
		}
	}
	echo $ShootingPlayer->getDisplayName();
	if ($TotalDamage > 0) {
		?> hits for a total of <span class="red"><?php echo $TotalDamage ?></span> damage in this round of combat<?php
	} else {
		?> does no damage this round<?php
		if (!$TraderResults['DeadBeforeShot']) {
			?>. Maybe they should go back to the academy<?php
		}
	} ?>.<br /><br /><?php
}
$TotalDamage = $TraderTeamCombatResults['TotalDamage']; ?>
This fleet <?php if ($TotalDamage > 0) { ?>hits for a total of <span class="red"><?php echo $TotalDamage ?></span> damage in this round of combat<?php } else { ?>does no damage this round. You call that a fleet? They need a better recruiter<?php } ?>.<br /><?php
foreach ($TraderTeamCombatResults['Downgrades'] as $structureID => $numDestroyed) { ?>
	This team destroys <span class="red"><?php echo pluralise($numDestroyed, (new PlanetStructureType($structureID, []))->name()); ?></span>.<br /><?php
}
