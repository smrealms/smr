<?php declare(strict_types=1);

/**
 * @var Smr\Template $this
 * @var ForceCombatResults $ForcesCombatResults
 */

$CombatForces = $ForcesCombatResults['Results'];
foreach ($CombatForces as $ForceType => $ForceResults) {
	$ShotHit = $ForceResults['Hit'];
	$ActualDamage = $ForceResults['ActualDamage'];
	$WeaponDamage = $ForceResults['WeaponDamage'];
	if (!isset($WeaponDamage['Launched'])) {
		throw new Exception('Force weapons must specify Launched');
	}
	$TargetPlayer = $ForceResults['Target'];
	?>
	<span class="cds"><?php echo $WeaponDamage['Launched']; ?></span><?php
	if ($ForceType === 'Mines') {
		?> mines kamikaze themselves against <?php
	} elseif ($ForceType === 'Drones') {
		?> combat drones launch at <?php
	} elseif ($ForceType === 'Scouts') {
		?> scout drones kamikaze themselves against <?php
	}

	if ($ShotHit && $ActualDamage['TargetAlreadyDead']) { ?> the debris that was once <?php }
	echo $TargetPlayer->getDisplayName();
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
		}
		$DamageTypes = 0;
		if ($ActualDamage['Shield'] > 0) { $DamageTypes += 1; }
		if ($ActualDamage['NumCDs'] > 0) { $DamageTypes += 1; }
		if ($ActualDamage['Armour'] > 0) { $DamageTypes += 1; }

		if ($ActualDamage['Shield'] > 0) {
			?><span class="shields"><?php echo number_format($ActualDamage['Shield']) ?></span> shields<?php
			$this->doDamageTypeReductionDisplay($DamageTypes);
		}
		if ($ActualDamage['NumCDs'] > 0) {
			?><span class="cds"><?php echo number_format($ActualDamage['NumCDs']) ?></span> drones<?php
			$this->doDamageTypeReductionDisplay($DamageTypes);
		}
		if ($ActualDamage['Armour'] > 0) {
			?><span class="red"><?php echo number_format($ActualDamage['Armour']) ?></span> plates of armour<?php
		}
	} ?>.
	<br /><?php
	if ($ShotHit && $ActualDamage['KillingShot']) {
		if (!isset($ForceResults['KillResults'])) {
			throw new Exception('KillingShot did not provide KillResults!');
		}
		$this->includeTemplate('includes/TraderCombatKillMessage.inc.php', ['KillResults' => $ForceResults['KillResults'], 'TargetPlayer' => $TargetPlayer]);
	}
}
if (isset($ForcesCombatResults['ForcesDestroyed']) && $ForcesCombatResults['ForcesDestroyed']) {
	?>Forces are <span class="red">DESTROYED!</span><br /><?php
}

$TotalDamage = $ForcesCombatResults['TotalDamage'] ?>
The forces <?php if ($TotalDamage > 0) { ?>hit for a total of <span class="red"><?php echo number_format($TotalDamage) ?></span> damage in this round of combat<?php } else { ?>do no damage this round<?php } ?>.
