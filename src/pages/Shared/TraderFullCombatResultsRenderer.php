<?php declare(strict_types=1);

namespace Smr\Pages\Shared;

use Smr\Combat\Results\Full\TraderFullCombatResults;
use Smr\Player;

class TraderFullCombatResultsRenderer {

	public static function render(
		bool $MinimalDisplay,
		TraderFullCombatResults $TraderCombatResults,
		?string $AttackLogLink,
		?Player $ThisPlayer,
	): void {
		// Determine damage done to ThisPlayer if they participated in this combat
		$ThisPlayerDamageTakenFromAttackers = null;
		$ThisPlayerDamageTakenFromDefenders = null;
		if ($ThisPlayer !== null) {
			$ThisPlayerCombatant = $ThisPlayer->getShip();
			if ($TraderCombatResults->defenders->hasCombatant($ThisPlayerCombatant)) {
				$ThisPlayerDamageTakenFromAttackers = $TraderCombatResults->attackers->getTotalDamageDealtTo(
					$ThisPlayerCombatant,
				);
			} elseif ($TraderCombatResults->attackers->hasCombatant($ThisPlayerCombatant)) {
				$ThisPlayerDamageTakenFromDefenders = $TraderCombatResults->defenders->getTotalDamageDealtTo(
					$ThisPlayerCombatant,
				);
			}
		}

		if ($MinimalDisplay) { ?>
			<h2>Attacker Results</h2><?php
		} else { ?>
			<h1>Attacker Results</h1><?php
		} ?>
		<br /><?php

		TraderTeamCombatResultsRenderer::render(
			TraderTeamCombatResults: $TraderCombatResults->attackers,
			MinimalDisplay: $MinimalDisplay,
			ThisPlayerDamageTaken: $ThisPlayerDamageTakenFromAttackers,
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
			TraderTeamCombatResults: $TraderCombatResults->defenders,
			MinimalDisplay: $MinimalDisplay,
			ThisPlayerDamageTaken: $ThisPlayerDamageTakenFromDefenders,
		);

		if ($MinimalDisplay) {
			echo $AttackLogLink;
		}

	}

}
