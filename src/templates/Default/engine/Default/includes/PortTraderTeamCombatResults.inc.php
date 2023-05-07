<?php declare(strict_types=1);

/**
 * @var Smr\Player $ThisPlayer
 * @var Smr\Template $this
 * @var bool $MinimalDisplay
 * @var PortAttackerCombatResults $TraderTeamCombatResults
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
			$TargetPort = $WeaponResults['TargetPort'];

			echo $ShootingPlayer->getDisplayName() ?> fires their <?php echo $ShootingWeapon->getName() ?> at <?php if ($ShotHit && $ActualDamage['TargetAlreadyDead']) { ?>the remnants of <?php } echo $TargetPort->getDisplayName();
			if (!$ShotHit || !$ActualDamage['TargetAlreadyDead']) {
				if (!$ShotHit) {
					?> and misses every critical system<?php
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
					?> destroying <?php
					$DamageTypes = 0;
					if ($ActualDamage['Shield'] > 0) { $DamageTypes = $DamageTypes + 1; }
					if ($ActualDamage['NumCDs'] > 0) { $DamageTypes = $DamageTypes + 1; }
					if ($ActualDamage['Armour'] > 0) { $DamageTypes = $DamageTypes + 1; }

					if ($ActualDamage['Shield'] > 0) {
						?><span class="shields"><?php echo number_format($ActualDamage['Shield']) ?></span> shields<?php
						$this->doDamageTypeReductionDisplay($DamageTypes);
					}
					if ($ActualDamage['NumCDs'] > 0) {
						?><span class="cds"><?php echo number_format($ActualDamage['NumCDs']) ?></span> combat drones<?php
						$this->doDamageTypeReductionDisplay($DamageTypes);
					}
					if ($ActualDamage['Armour'] > 0) {
						?><span class="red"><?php echo number_format($ActualDamage['Armour']) ?></span> plates of armour<?php
					}
				}
			} ?>.
			<br /><?php
			if ($ShotHit && $ActualDamage['KillingShot']) {
				if (!isset($WeaponResults['KillResults'])) {
					throw new Exception('KillingShot did not provide KillResults!');
				}
				$this->includeTemplate('includes/PortKillMessage.inc.php', ['KillResults' => $WeaponResults['KillResults'], 'TargetPort' => $TargetPort, 'ShootingPlayer' => $ShootingPlayer]);
			}
		}
		if (isset($TraderResults['Drones'])) {
			$Drones = $TraderResults['Drones'];
			$ActualDamage = $Drones['ActualDamage'];
			$WeaponDamage = $Drones['WeaponDamage'];
			$TargetPort = $Drones['TargetPort'];

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
				echo $TargetPort->getDisplayName();

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
						?> destroying <?php
						$DamageTypes = 0;
						if ($ActualDamage['Shield'] > 0) { $DamageTypes = $DamageTypes + 1; }
						if ($ActualDamage['NumCDs'] > 0) { $DamageTypes = $DamageTypes + 1; }
						if ($ActualDamage['Armour'] > 0) { $DamageTypes = $DamageTypes + 1; }

						if ($ActualDamage['Shield'] > 0) {
							?><span class="shields"><?php echo number_format($ActualDamage['Shield']) ?></span> shields<?php
							$this->doDamageTypeReductionDisplay($DamageTypes);
						}
						if ($ActualDamage['NumCDs'] > 0) {
							?><span class="cds"><?php echo number_format($ActualDamage['NumCDs']) ?></span> combat drones<?php
							$this->doDamageTypeReductionDisplay($DamageTypes);
						}
						if ($ActualDamage['Armour'] > 0) {
							?><span class="red"><?php echo number_format($ActualDamage['Armour']) ?></span> plates of armour<?php
						}
					}
				}
			} ?>.
			<br /><?php
			if ($ActualDamage['KillingShot']) {
				if (!isset($Drones['KillResults'])) {
					throw new Exception('KillingShot did not provide KillResults!');
				}
				$this->includeTemplate('includes/PortKillMessage.inc.php', ['KillResults' => $Drones['KillResults'], 'TargetPort' => $TargetPort, 'ShootingPlayer' => $ShootingPlayer]);
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
This fleet <?php if ($TotalDamage > 0) { ?>hits for a total of <span class="red"><?php echo $TotalDamage ?></span> damage in this round of combat<?php } else { ?>does no damage this round. You call that a fleet? They need a better recruiter<?php } ?>.<br />

<?php
$Downgrades = $TraderTeamCombatResults['Downgrades'];
if ($Downgrades !== 0) {
	?>The port has lost <?php echo pluralise($Downgrades, 'level'); ?>.<?php
}
