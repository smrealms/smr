<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

use Smr\Combat\Results\Combatant\TeamCombatResults;
use Smr\Combat\Results\Weapon\HitWeaponResult;
use Smr\Player;
use Smr\Template;

class TraderTeamCombatResultsRenderer {

	/** @param TeamCombatResults<\Smr\Combat\NormalCombatantInterface> $TraderTeamCombatResults */
	public static function render(
		Template $template,
		?Player $ThisPlayer,
		bool $MinimalDisplay,
		TeamCombatResults $TraderTeamCombatResults,
	): void {
		$CombatTeamResults = $TraderTeamCombatResults->traders;
		foreach ($CombatTeamResults as $TraderResults) {
			$ShootingPlayer = $TraderResults->combatant;
			$TotalDamage = $TraderResults->getTotalDamage();

			if ($MinimalDisplay) {
				echo $ShootingPlayer->getCombatName();
				if ($TotalDamage > 0) { ?>
					hits for a total of <span class="red"><?php echo $TotalDamage ?></span> damage in this round of combat<?php
				} else { ?>
					does no damage this round<?php
				} ?>.<br /><br /><?php
				continue;
			}

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
					$TargetPlayer = $WeaponResult->target;

					echo $ShootingPlayer->getCombatName() ?> fires their <?php echo $ShootingWeapon->getName() ?> at <?php
					if ($ShotHit && $ActualDamage->targetAlreadyDead) {
						?>the debris that was once <?php
					}
					echo $TargetPlayer->getCombatName();
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
				if ($TraderResults->dronesResult !== null) {
					$Drones = $TraderResults->dronesResult;
					$ActualDamage = $Drones->actualDamage;
					$WeaponDamage = $Drones->weaponDamage;
					$TargetPlayer = $Drones->target;

					echo $ShootingPlayer->getCombatName();
					if ($WeaponDamage->launched === 0) {
						?> fails to launch their combat drones<?php
					} else {
						?> launches <span class="cds"><?php echo $WeaponDamage->launched ?></span> combat drones at <?php
						if ($ActualDamage->targetAlreadyDead) {
							?>the debris that was once <?php
						}
						echo $TargetPlayer->getCombatName();
						if (!$ActualDamage->targetAlreadyDead) {
							if ($ActualDamage->totalDamage === 0) {
								if ($WeaponDamage->shieldDamage > 0) {
									if ($ActualDamage->hasCombatDrones) {
										?> which prove ineffective against their combat drones<?php
									} else {
										?> which washes harmlessly over their hull<?php
									}
								}
								if ($ActualDamage->armourDamage > 0) {
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
		$TotalDamage = $TraderTeamCombatResults->totalDamage;

		$TotalDamageToThisPlayer = 0;
		foreach ($TraderTeamCombatResults->traders as $TraderResults) {
			// Check if ThisPlayer was a target in this round of combat
			$ThisAccountID = $ThisPlayer?->getAccountID();
			$totalDamagePerTarget = $TraderResults->getTotalDamagePerTarget();
			if ($ThisAccountID === null || !isset($totalDamagePerTarget[$ThisAccountID])) {
				$TotalDamageToThisPlayer = null;
				break;
			}
			$TotalDamageToThisPlayer += $totalDamagePerTarget[$ThisAccountID];
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

		<?php
	}

}
