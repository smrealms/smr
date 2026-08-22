<?php declare(strict_types=1);

namespace Smr\Combat\Results\Kill;

use Smr\Pages\Shared\CombatKillMessageRenderer;

interface KillResultInterface {

	public function render(CombatKillMessageRenderer $renderer): void;

}
