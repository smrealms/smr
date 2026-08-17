<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Combat\Results\TraderFullCombatResults;
use Smr\Pages\Shared\Admin\CombatSimTeamDetailsRenderer;
use Smr\Pages\Shared\TraderFullCombatResultsRenderer;
use Smr\Template;

class CombatSimulatorRenderer {

/**
 * @param array<string> $DummyNames
 * @param array<?\Smr\Player> $Attackers
 * @param array<?\Smr\Player> $Defenders
 */
public static function render(
	Template $template,
	string $EditDummysLink,
	array $DummyNames,
	array $Attackers,
	array $Defenders,
	bool $Duplicates,
	CombatSimulatorProcessor $CombatSimPage,
	?TraderFullCombatResults $TraderCombatResults,
): void {
?>
<a href="<?php echo $EditDummysLink ?>">Edit Combat Dummys</a><br /><br />

<?php if ($Duplicates) { ?><h1>Do not use duplicate dummy names, they would interfere with each other</h1><?php } ?>

<form action="<?php echo $CombatSimPage->href() ?>" method="POST">
	<table class="fullwidth">
		<tr>
			<th>Attackers</th>
			<th>Defenders</th>
		<tr>
			<td class="top">
				<?php CombatSimTeamDetailsRenderer::render(Team: $Attackers, DummyNames: $DummyNames, MemberInputName: 'attackers'); ?>
			</td>
			<td class="top">
				<?php CombatSimTeamDetailsRenderer::render(Team: $Defenders, DummyNames: $DummyNames, MemberInputName: 'defenders'); ?>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="center">
				<br />All drones, shields, armour assumed full at the start of the simulation<br /><br />
				<?php echo $CombatSimPage->actionUpdate->html('Update Details'); ?>&nbsp;
				<?php echo $CombatSimPage->actionRepair->html('Repair All'); ?>&nbsp;
				<?php echo $CombatSimPage->actionRun->html('Run Simulation'); ?>&nbsp;
				<?php echo $CombatSimPage->actionRunAll->html('Run Simulation TO THE DEATH!!'); ?>
			</td>
		</tr>
	</table>
</form><?php
if (isset($TraderCombatResults)) {
	TraderFullCombatResultsRenderer::render(
		template: $template,
		MinimalDisplay: false,
		TraderCombatResults: $TraderCombatResults,
		AttackLogLink: null,
		ThisPlayer: null,
	);
	?><br /><?php
}

}

}
