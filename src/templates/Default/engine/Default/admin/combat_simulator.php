<?php declare(strict_types=1);

/**
 * @var Smr\Pages\Admin\CombatSimulatorProcessor $CombatSimPage
 */

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
				<?php $this->includeTemplate('admin/includes/CombatSimTeamDetails.inc.php', ['Team' => $Attackers, 'MemberDescription' => 'Attacker', 'MemberInputName' => 'attackers']); ?>
			</td>
			<td class="top">
				<?php $this->includeTemplate('admin/includes/CombatSimTeamDetails.inc.php', ['Team' => $Defenders, 'MemberDescription' => 'Defender', 'MemberInputName' => 'defenders']); ?>
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
	$this->includeTemplate('includes/TraderFullCombatResults.inc.php');
	?><br /><?php
}
