<?php
if (is_array($TraderTeamCombatResults['Traders'])) {
	foreach ($TraderTeamCombatResults['Traders'] as $AccountID => $TraderResults) {
		$ShootingPlayer = $TraderResults['Player'];
		$TotalDamage = $TraderResults['TotalDamage'];

		if ($MinimalDisplay) {
			echo $ShootingPlayer->getDisplayName();
			if ($TotalDamage > 0) { ?>
				hits for a total of <span class="red"><?php echo $TotalDamage ?></span> damage in this round of combat<?php
			} else { ?>
				does no damage this round<?php
			} ?>.<br /><br /><?php
			continue;
		}

		if ($TraderResults['DeadBeforeShot']) {
			echo $ShootingPlayer->getDisplayName() ?> died before they were able to attack!<br /><?php
		} else {
			if (isset($TraderResults['Weapons']) && is_array($TraderResults['Weapons'])) {
				foreach ($TraderResults['Weapons'] as $WeaponResults) {
					$ShootingWeapon = $WeaponResults['Weapon'];
					$ShotHit = $WeaponResults['Hit'];
					if ($ShotHit) {
						$ActualDamage = $WeaponResults['ActualDamage'];
						$WeaponDamage = $WeaponResults['WeaponDamage'];
					}
					$TargetPlayer = $WeaponResults['TargetPlayer'];

					echo $ShootingPlayer->getDisplayName() ?> fires their <?php echo $ShootingWeapon->getName() ?> at <?php
					if ($ShotHit && $ActualDamage['TargetAlreadyDead']) {
						?>the debris that was once <?php
					}
					echo $TargetPlayer->getDisplayName();
					if (!$ShotHit || !$ActualDamage['TargetAlreadyDead']) {
						if (!$ShotHit) {
							?> and misses<?php
						} elseif ($ActualDamage['TotalDamage'] == 0) {
							if ($WeaponDamage['Shield'] > 0) {
								if ($ActualDamage['HasCDs']) {
									?> which proves ineffective against their combat drones<?php
								} else {
									?> which washes harmlessly over their hull<?php
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
						$this->includeTemplate('includes/TraderCombatKillMessage.inc.php', ['KillResults' => $WeaponResults['KillResults'], 'TargetPlayer' => $TargetPlayer, 'ShootingPlayer' => $ShootingPlayer]);
					}
				}
			}
			if (isset($TraderResults['Drones'])) {
				$Drones = $TraderResults['Drones'];
				$ActualDamage = $Drones['ActualDamage'];
				$WeaponDamage = $Drones['WeaponDamage'];
				$TargetPlayer = $Drones['TargetPlayer'];
				$DamageTypes = 0;
				if ($ActualDamage['Shield'] > 0) { $DamageTypes = $DamageTypes + 1; }
				if ($ActualDamage['NumCDs'] > 0) { $DamageTypes = $DamageTypes + 1; }
				if ($ActualDamage['Armour'] > 0) { $DamageTypes = $DamageTypes + 1; }

				echo $ShootingPlayer->getDisplayName();
				if ($WeaponDamage['Launched'] == 0) {
					?> fails to launch their combat drones<?php
				} else {
					?> launches <span class="cds"><?php echo $WeaponDamage['Launched'] ?></span> combat drones at <?php
					if ($ActualDamage['TargetAlreadyDead']) {
						?>the debris that was once <?php
					}
					echo $TargetPlayer->getDisplayName();
					if (!$ActualDamage['TargetAlreadyDead']) {
						if ($ActualDamage['TotalDamage'] == 0) {
							if ($WeaponDamage['Shield'] > 0) {
								if ($ActualDamage['HasCDs']) {
									?> which prove ineffective against their combat drones<?php
								} else {
									?> which washes harmlessly over their hull<?php
								}
							}
							if ($ActualDamage['Armour'] > 0) {
								?> which is deflected by their shields<?php
							} else {
								?> but they cannot do any damage<?php
							}
						} else {
							?> destroying <?php
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
					$this->includeTemplate('includes/TraderCombatKillMessage.inc.php', ['KillResults' => $Drones['KillResults'], 'TargetPlayer' => $TargetPlayer, 'ShootingPlayer' => $ShootingPlayer]);
				}
			}
		}
		echo $ShootingPlayer->getDisplayName();
		if ($TotalDamage > 0) {
			?> hits for a total of <span class="red"><?php echo $TotalDamage ?></span> damage in this round of combat<?php
		} else {
			?> does no damage this round.<?php
			if (!$TraderResults['DeadBeforeShot']) {
				?> Maybe they should go back to the academy<?php
			}
		} ?>.<br /><br /><?php
	}
}
$TotalDamage = $TraderTeamCombatResults['TotalDamage'];

$TotalDamageToThisPlayer = 0;
foreach ($TraderTeamCombatResults['Traders'] as $AccountID => $TraderResults) {
	// Check if ThisPlayer was a target in this round of combat
	if (!isset($TraderResults['TotalDamagePerTargetPlayer'][$ThisPlayer->getAccountID()])) {
		$TotalDamageToThisPlayer = null;
		break;
	}
	$TotalDamageToThisPlayer += $TraderResults['TotalDamagePerTargetPlayer'][$ThisPlayer->getAccountID()];
} ?>

This fleet <?php
if ($TotalDamage > 0) { ?>
	hits for a total of <span class="red"><?php echo $TotalDamage ?></span> damage in this round of combat<?php
	if ($TotalDamageToThisPlayer !== null) {
		?>, of which <span class="red"><?php echo $TotalDamageToThisPlayer; ?></span> was done to you<?php
	}
} else { ?>
	does no damage this round. You call that a fleet? They need a better recruiter<?php
} ?>.
