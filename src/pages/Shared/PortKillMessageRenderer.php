<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

use Smr\Player;
use Smr\Port;

class PortKillMessageRenderer {

/**
 * @param array{KillerCredits?: int, ...} $KillResults
 */
public static function render(
	Player $ShootingPlayer,
	Port $TargetPort,
	array $KillResults,
): void {
echo $TargetPort->getDisplayName() ?>'s defenses are <span class="red">DESTROYED!</span><br /><?php
if (isset($KillResults['KillerCredits'])) {
	echo $ShootingPlayer->getDisplayName() ?> claims <span class="creds"><?php echo number_format($KillResults['KillerCredits']) ?></span> credits from the port.<br /><?php
}

}

}
