<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

use Smr\Combat\Results\TraderFullCombatResults;
use Smr\Player;
use Smr\Template;

class TraderFullCombatResultsRenderer {

public static function render(
	Template $template,
	bool $MinimalDisplay,
	TraderFullCombatResults $TraderCombatResults,
	?string $AttackLogLink,
	?Player $ThisPlayer,
): void {
if ($MinimalDisplay) { ?>
	<h2>Attacker Results</h2><?php
} else { ?>
	<h1>Attacker Results</h1><?php
} ?>
<br /><?php

TraderTeamCombatResultsRenderer::render(
	template: $template,
	TraderTeamCombatResults: $TraderCombatResults->attackers,
	MinimalDisplay: $MinimalDisplay,
	ThisPlayer: $ThisPlayer,
); ?>

<br /><br /><?php
if ($MinimalDisplay) { ?>
	<h2>Defender Results</h2><?php
} else { ?>
	<img src="images/creonti_cruiser.jpg" alt="Creonti Cruiser" title="Creonti Cruiser"><br />
	<br />
	<h1>Defender Results</h1><?php
} ?>
<br /><?php

TraderTeamCombatResultsRenderer::render(
	template: $template,
	TraderTeamCombatResults: $TraderCombatResults->defenders,
	MinimalDisplay: $MinimalDisplay,
	ThisPlayer: $ThisPlayer,
);

if ($MinimalDisplay) {
	echo $AttackLogLink;
}

}

}
