<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

use Smr\Combat\Results\Combatant\ForceCombatResults;
use Smr\Template;

class ForcesCombatResultsRenderer {

	public static function render(
		Template $template,
		ForceCombatResults $ForcesCombatResults,
	): void {
		$CombatForces = $ForcesCombatResults->results;
		foreach ($CombatForces as $ForceType => $ForceResult) {
			$ActualDamage = $ForceResult->actualDamage;
			$WeaponDamage = $ForceResult->weaponDamage;
			$TargetPlayer = $ForceResult->target;
			?>
			<span class="cds"><?php echo $WeaponDamage->launched; ?></span><?php
			if ($ForceType === 'Mines') {
				?> mines kamikaze themselves against <?php
			} elseif ($ForceType === 'Drones') {
				?> combat drones launch at <?php
			} elseif ($ForceType === 'Scouts') {
				?> scout drones kamikaze themselves against <?php
			}

			if ($ActualDamage->targetAlreadyDead) { ?> the debris that was once <?php }
			echo $TargetPlayer->getCombatName();
			if (!$ActualDamage->targetAlreadyDead) {
				if ($ActualDamage->totalDamage === 0) {
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
			if ($ForceResult->killResult !== null) {
				CombatKillMessageRenderer::render($ForceResult->killResult);
			}
		}
		if ($ForcesCombatResults->forceDestroyed) {
			?>Forces are <span class="red">DESTROYED!</span><br /><?php
		}

		$TotalDamage = $ForcesCombatResults->totalDamage ?>
		The forces <?php if ($TotalDamage > 0) { ?>hit for a total of <span class="red"><?php echo number_format($TotalDamage) ?></span> damage in this round of combat<?php } else { ?>do no damage this round<?php } ?>.

		<?php
	}

}
