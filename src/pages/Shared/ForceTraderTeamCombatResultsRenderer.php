<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

use Smr\Combat\Results\Combatant\TeamCombatResults;
use Smr\Combat\Results\Weapon\HitWeaponResult;
use Smr\Template;

class ForceTraderTeamCombatResultsRenderer {

	/** @param TeamCombatResults<\Smr\Force> $TraderTeamCombatResults */
	public static function render(
		Template $template,
		TeamCombatResults $TraderTeamCombatResults,
	): void {
		$AllTraderResults = $TraderTeamCombatResults->traders;
		foreach ($AllTraderResults as $TraderResults) {
			$ShootingPlayer = $TraderResults->combatant;
			$TotalDamage = $TraderResults->getTotalDamage();
			if ($TraderResults->deadBeforeShot) {
				echo $ShootingPlayer->getCombatName() ?> died before they were able to attack!<br /><?php
			} else {
				foreach ($TraderResults->weaponResults as $WeaponResult) {
					$ShootingWeapon = $WeaponResult->weapon;
					$ShotHit = $WeaponResult instanceof HitWeaponResult;
					if ($ShotHit) {
						$ActualDamage = $WeaponResult->actualDamage;
						$WeaponDamage = $WeaponResult->weaponDamage;
					}

					echo $ShootingPlayer->getCombatName() ?> fires their <?php echo $ShootingWeapon->getName() ?> at<?php if ($ShotHit && $ActualDamage->targetAlreadyDead) { ?> the debris that was once<?php } ?> the forces<?php
					if (!$ShotHit || !$ActualDamage->targetAlreadyDead) {
						if (!$ShotHit) {
							?> and misses<?php
						} elseif ($ActualDamage->totalDamage === 0) {
							if ($WeaponDamage->shieldDamage > 0) {
								?> which proves ineffective against the <?php if ($ActualDamage->hasMines) { ?>mines<?php } elseif ($ActualDamage->hasCombatDrones) { ?>combat drones<?php } else { ?>scout drones<?php }
							} elseif ($WeaponDamage->armourDamage > 0) {
								?> which is deflected by the <?php if ($ActualDamage->hasMines) { ?>mines<?php } elseif ($ActualDamage->hasCombatDrones) { ?>combat drones<?php } else { ?>scout drones<?php } ?> shields<?php
							} else {
								?> but it cannot do any damage<?php
							}
						} else {
							?> destroying <?php echo $template->displayForceTakenDamage($ActualDamage);
						}
					} ?>.
					<br />
					<?php if ($ShotHit && $ActualDamage->killingShot) {
						?>Forces are <span class="red">DESTROYED!</span><br /><?php
					}
				}
				if (isset($TraderResults->dronesResult)) {
					$Drones = $TraderResults->dronesResult;
					$ActualDamage = $Drones->actualDamage;
					$WeaponDamage = $Drones->weaponDamage;

					echo $ShootingPlayer->getCombatName();
					if ($WeaponDamage->launched === 0) {
						?> fails to launch their combat drones<?php
					} else {
						?> launches <span class="cds"><?php echo $WeaponDamage->launched ?></span> combat drones at<?php if ($ActualDamage->targetAlreadyDead) { ?> the debris that was once <?php } ?> the forces<?php
						if (!$ActualDamage->targetAlreadyDead) {
							if ($ActualDamage->totalDamage === 0) {
								if ($WeaponDamage->shieldDamage > 0) {
									?> which prove ineffective against the <?php if ($ActualDamage->hasMines) { ?>mines<?php } elseif ($ActualDamage->hasCombatDrones) { ?>combat drones<?php } else { ?>scout drones<?php }
								} elseif ($WeaponDamage->armourDamage > 0) {
									?> which is deflected by the <?php
									if ($ActualDamage->hasMines) { ?>mines<?php } elseif ($ActualDamage->hasCombatDrones) { ?>combat drones<?php } else { ?>scout drones<?php } ?> shields<?php
								} else {
									?> but they cannot do any damage<?php
								}
							} else {
								$DamageTypes = 0;
								if ($ActualDamage->numMines > $WeaponDamage->kamikaze) { $DamageTypes += 1; }
								if ($ActualDamage->numCombatDrones > 0) { $DamageTypes += 1; }
								if ($ActualDamage->numScoutDrones > 0) { $DamageTypes += 1; }

								if ($WeaponDamage->kamikaze === 0) {
									?> destroying <?php
								} else {
									?> of which <span class="cds"><?php echo $WeaponDamage->kamikaze ?></span> kamikaze against <span class="red"><?php echo $WeaponDamage->kamikaze ?></span> mines<?php
									if ($DamageTypes > 0) {
										?> whilst the others destroy <?php
									}
								}
								echo $template->displayForceTakenDamage($ActualDamage, $WeaponDamage->kamikaze);
							}
						}
					}?>.
					<br />
					<?php if ($ActualDamage->killingShot) {
						?>Forces are <span class="red">DESTROYED!</span><br /><?php
					}
				}
			}
			echo $ShootingPlayer->getCombatName();
			if ($TotalDamage > 0) {
				?> hits for a total of <span class="red"><?php echo $TotalDamage ?></span> damage in this round of combat<?php
			} else {
				?> does no damage this round.<?php
				if (!$TraderResults->deadBeforeShot) {
					?> Maybe they should go back to the academy<?php
				}
			} ?>.<br /><br /><?php
		}
		$TotalDamage = $TraderTeamCombatResults->totalDamage; ?>
		This fleet <?php if ($TotalDamage > 0) { ?>hits for a total of <span class="red"><?php echo $TotalDamage ?></span> damage in this round of combat<?php } else { ?>does no damage this round. You call that a fleet? They need a better recruiter<?php } ?>.

		<?php
	}

}
