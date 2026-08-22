<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

use Smr\Combat\Results\Combatant\CombatantResult;
use Smr\Combat\Results\Weapon\HitWeaponResult;
use Smr\Player;
use Smr\Template;

class PlanetCombatResultsRenderer {

	/**
	 * @param CombatantResult<\Smr\Combat\NormalCombatantInterface> $PlanetCombatResults
	 */
	public static function render(
		Template $template,
		Player $ThisPlayer,
		bool $MinimalDisplay,
		?string $AttackLogLink,
		CombatantResult $PlanetCombatResults,
	): void {
		$CombatPlanet = $PlanetCombatResults->combatant;
		$TotalDamage = $PlanetCombatResults->getTotalDamage();
		if ($MinimalDisplay) {
			echo $CombatPlanet->getCombatName();
			if ($TotalDamage > 0) {
				?> hit for a total of <span class="red"><?php echo $TotalDamage; ?></span> damage in this round of combat of which <span class="red"><?php echo $PlanetCombatResults->getTotalDamagePerTarget()[$ThisPlayer->getAccountID()]; ?></span> was done to you<?php
			} else {
				?> does no damage this round<?php
			} ?>. <?php echo $AttackLogLink;
			return;
		}
		foreach ($PlanetCombatResults->weaponResults as $WeaponResult) {
			$ShootingWeapon = $WeaponResult->weapon;
			$ShotHit = $WeaponResult instanceof HitWeaponResult;
			if ($ShotHit) {
				$ActualDamage = $WeaponResult->actualDamage;
				$WeaponDamage = $WeaponResult->weaponDamage;
			}
			$TargetPlayer = $WeaponResult->target;

			echo $CombatPlanet->getCombatName() ?> fires a <?php echo $ShootingWeapon->getName(); ?> at <?php if ($ShotHit && $ActualDamage->targetAlreadyDead) { ?> the debris that was once <?php } echo $TargetPlayer->getCombatName();
			if (!$ShotHit || !$ActualDamage->targetAlreadyDead) {
				if (!$ShotHit) {
					?> and misses<?php
				} elseif ($ActualDamage->totalDamage === 0) {
					if ($WeaponDamage->shieldDamage > 0) {
						if ($ActualDamage->hasCombatDrones) {
							?> which proves ineffective against their combat drones<?php
						} else {
							?> which washes harmlessly over their hull<?php
						}
					} elseif ($WeaponDamage->armourDamage > 0) {
						?> which is deflected by their shields<?php
					} else {
						?> but it cannot do any damage<?php
					}
				} else {
					?> destroying <?php echo $template->displayTakenDamage($ActualDamage);
				}
			} ?>.
			<br /><?php
			if ($ShotHit && $WeaponResult->killResult !== null) {
				CombatKillMessageRenderer::render($WeaponResult->killResult);
			}
		}
		if (isset($PlanetCombatResults->dronesResult)) {
			$Drones = $PlanetCombatResults->dronesResult;
			$ActualDamage = $Drones->actualDamage;
			$WeaponDamage = $Drones->weaponDamage;
			$TargetPlayer = $Drones->target;

			echo $CombatPlanet->getCombatName();
			if ($WeaponDamage->launched === 0) {
				?> fails to launch it's combat drones<?php
			} else {
				?> launches <span class="cds"><?php echo $WeaponDamage->launched ?></span> combat drones at <?php if ($ActualDamage->targetAlreadyDead) { ?>the debris that was once <?php } echo $TargetPlayer->getCombatName();
				if (!$ActualDamage->targetAlreadyDead) {
					if ($ActualDamage->totalDamage === 0) {
						if ($WeaponDamage->shieldDamage > 0) {
							if ($ActualDamage->hasCombatDrones) {
								?> which prove ineffective against their combat drones<?php
							} else {
								?> which washes harmlessly over their hull<?php
							}
						} elseif ($WeaponDamage->armourDamage > 0) {
							?> which is deflected by their shields<?php
						} else {
							?> but they cannot do any damage<?php
						}
					} else {
						?> destroying <?php echo $template->displayTakenDamage($ActualDamage);
					}
				}
			} ?>.
			<br /><?php
			if ($Drones->killResult !== null) {
				CombatKillMessageRenderer::render($Drones->killResult);
			}
		}

		echo $CombatPlanet->getCombatName();
		if ($TotalDamage > 0) {
			?> hit for a total of <span class="red"><?php echo $TotalDamage; ?></span> damage in this round of combat<?php
		} else {
			?> does no damage this round. You call that a planet? It needs a better builder<?php
		} ?>.

		<?php
	}

}
