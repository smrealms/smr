<?php declare(strict_types=1);

/**
 * @var Smr\Player $ThisPlayer
 * @var Smr\Template $this
 * @var bool $MinimalDisplay
 * @var ?string $AttackLogLink
 * @var PlanetCombatResults $PlanetCombatResults
 */

$CombatPlanet = $PlanetCombatResults['Planet'];
$TotalDamage = $PlanetCombatResults['TotalDamage'];
if ($MinimalDisplay) {
	echo $CombatPlanet->getCombatName();
	if ($TotalDamage > 0) {
		?> hit for a total of <span class="red"><?php echo $TotalDamage; ?></span> damage in this round of combat of which <span class="red"><?php echo $PlanetCombatResults['TotalDamagePerTargetPlayer'][$ThisPlayer->getAccountID()]; ?></span> was done to you<?php
	} else {
		?> does no damage this round<?php
	} ?>. <?php echo $AttackLogLink;
	return;
}
if (isset($PlanetCombatResults['Weapons'])) {
	foreach ($PlanetCombatResults['Weapons'] as $WeaponResults) {
		$ShootingWeapon = $WeaponResults['Weapon'];
		$ShotHit = $WeaponResults['Hit'];
		if ($ShotHit) {
			if (!isset($WeaponResults['ActualDamage']) || !isset($WeaponResults['WeaponDamage'])) {
				throw new Exception('Weapon hit without providing damage!');
			}
			$ActualDamage = $WeaponResults['ActualDamage'];
			$WeaponDamage = $WeaponResults['WeaponDamage'];
		}
		$TargetPlayer = $WeaponResults['Target'];

		echo $CombatPlanet->getCombatName() ?> fires a <?php echo $ShootingWeapon->getName(); ?> at <?php if ($ShotHit && $ActualDamage['TargetAlreadyDead']) { ?> the debris that was once <?php } echo $TargetPlayer->getDisplayName();
		if (!$ShotHit || !$ActualDamage['TargetAlreadyDead']) {
			if (!$ShotHit) {
				?> and misses<?php
			} elseif ($ActualDamage['TotalDamage'] === 0) {
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
				if ($ActualDamage['Shield'] > 0) { $DamageTypes += 1; }
				if ($ActualDamage['NumCDs'] > 0) { $DamageTypes += 1; }
				if ($ActualDamage['Armour'] > 0) { $DamageTypes += 1; }

				if ($ActualDamage['Shield'] > 0) {
					?><span class="shields"><?php echo number_format($ActualDamage['Shield']); ?></span> shields<?php
					$this->doDamageTypeReductionDisplay($DamageTypes);
				}
				if ($ActualDamage['NumCDs'] > 0) {
					?><span class="cds"><?php echo number_format($ActualDamage['NumCDs']); ?></span> combat drones<?php
					$this->doDamageTypeReductionDisplay($DamageTypes);
				}
				if ($ActualDamage['Armour'] > 0) {
					?><span class="red"><?php echo number_format($ActualDamage['Armour']); ?></span> plates of armour<?php
				}
			}
		} ?>.
		<br /><?php
		if ($ShotHit && $ActualDamage['KillingShot']) {
			if (!isset($WeaponResults['KillResults'])) {
				throw new Exception('KillingShot did not provide KillResults!');
			}
			$this->includeTemplate('includes/TraderCombatKillMessage.inc.php', ['KillResults' => $WeaponResults['KillResults'], 'TargetPlayer' => $TargetPlayer]);
		}
	}
}
if (isset($PlanetCombatResults['Drones'])) {
	$Drones = $PlanetCombatResults['Drones'];
	$ActualDamage = $Drones['ActualDamage'];
	$WeaponDamage = $Drones['WeaponDamage'];
	$TargetPlayer = $Drones['Target'];
	$DamageTypes = 0;
	if ($ActualDamage['Shield'] > 0) { $DamageTypes += 1; }
	if ($ActualDamage['NumCDs'] > 0) { $DamageTypes += 1; }
	if ($ActualDamage['Armour'] > 0) { $DamageTypes += 1; }

	echo $CombatPlanet->getCombatName();
	if (!isset($WeaponDamage['Launched'])) {
		throw new Exception('Drone weapons must specify Launched');
	}
	if ($WeaponDamage['Launched'] === 0) {
		?> fails to launch it's combat drones<?php
	} else {
		?> launches <span class="cds"><?php echo $WeaponDamage['Launched'] ?></span> combat drones at <?php if ($ActualDamage['TargetAlreadyDead']) { ?>the debris that was once <?php } echo $TargetPlayer->getDisplayName();
		if (!$ActualDamage['TargetAlreadyDead']) {
			if ($ActualDamage['TotalDamage'] === 0) {
				if ($WeaponDamage['Shield'] > 0) {
					if ($ActualDamage['HasCDs']) {
						?> which prove ineffective against their combat drones<?php
					} else {
						?> which washes harmlessly over their hull<?php
					}
				} elseif ($WeaponDamage['Armour'] > 0) {
					?> which is deflected by their shields<?php
				} else {
					?> but they cannot do any damage<?php
				}
			} else {
				?> destroying <?php
				if ($ActualDamage['Shield'] > 0) {
					?><span class="shields"><?php echo number_format($ActualDamage['Shield']); ?></span> shields<?php
					$this->doDamageTypeReductionDisplay($DamageTypes);
				}
				if ($ActualDamage['NumCDs'] > 0) {
					?><span class="cds"><?php echo number_format($ActualDamage['NumCDs']); ?></span> combat drones<?php
					$this->doDamageTypeReductionDisplay($DamageTypes);
				}
				if ($ActualDamage['Armour'] > 0) {
					?><span class="red"><?php echo number_format($ActualDamage['Armour']); ?></span> plates of armour<?php
				}
			}
		}
	} ?>.
	<br /><?php
	if ($ActualDamage['KillingShot']) {
		if (!isset($Drones['KillResults'])) {
			throw new Exception('KillingShot did not provide KillResults!');
		}
		$this->includeTemplate('includes/TraderCombatKillMessage.inc.php', ['KillResults' => $Drones['KillResults'], 'TargetPlayer' => $TargetPlayer]);
	}
}

echo $CombatPlanet->getCombatName();
if ($TotalDamage > 0) {
	?> hit for a total of <span class="red"><?php echo $TotalDamage; ?></span> damage in this round of combat<?php
} else {
	?> does no damage this round. You call that a planet? It needs a better builder<?php
} ?>.
