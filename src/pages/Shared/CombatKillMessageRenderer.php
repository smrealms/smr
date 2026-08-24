<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

use Smr\Combat\Results\Kill\ForcesDestroyedByPlayer;
use Smr\Combat\Results\Kill\KillResultInterface;
use Smr\Combat\Results\Kill\PlanetDestroyedByPlayer;
use Smr\Combat\Results\Kill\PlayerKilledByEnvironment;
use Smr\Combat\Results\Kill\PlayerKilledByPlayer;
use Smr\Combat\Results\Kill\PortDestroyedByPlayer;

final class CombatKillMessageRenderer {

	public static function render(KillResultInterface $killResult): void {
		$killResult->render(new self());
	}

	public function renderPlayerKilledByPlayer(PlayerKilledByPlayer $result): void {
		echo $result->target->getCombatName(); ?> has been <span class="red">DESTROYED</span>, losing <span class="exp"><?php echo number_format($result->deadExp) ?></span> experience.<br /><?php
		echo $result->killer->getCombatName(); ?> salvages <span class="creds"><?php echo number_format($result->killerCredits) ?></span> credits from the wreckage and gains <span class="exp"><?php echo number_format($result->killerExp) ?></span> experience.<br /><?php
	}

	public function renderPlayerKilledByEnvironment(PlayerKilledByEnvironment $result): void {
		echo $result->target->getCombatName(); ?> has been <span class="red">DESTROYED</span>, losing <span class="exp"><?php echo number_format($result->deadExp) ?></span> experience.<br /><?php
		echo 'The <span class="creds"> ' . number_format($result->lostCredits) . '</span> credits that were onboard ' . $result->target->getCombatName() . "'s ship are lost in the wreckage.<br />";
	}

	public function renderPortDestroyedByPlayer(PortDestroyedByPlayer $result): void {
		echo $result->target->getCombatName() ?>'s defenses are <span class="red">DESTROYED!</span><br /><?php
	}

	public function renderPlanetDestroyedByPlayer(PlanetDestroyedByPlayer $result): void {
		echo $result->target->getCombatName() ?>'s defenses are <span class="red">DESTROYED!</span><br /><?php
	}

	public function renderForcesDestroyedByPlayer(ForcesDestroyedByPlayer $result): void {
	}

}
