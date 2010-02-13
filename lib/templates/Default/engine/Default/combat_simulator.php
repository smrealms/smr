<a href="<?php echo $EditDummysLink ?>">Edit Combat Dummys</a><br /><br />

<?php if($Duplicates){ ?><h1>Do not use duplicate dummy names, they would interfere with each other</h1><?php } ?>

<form action="<?php echo $CombatSimHREF ?>" method="POST">
	<table>
		<tr>
			<td style="vertical-align:top">
				Attackers<br />
				<?php $this->includeTemplate('includes/CombatSimTeamDetails.inc',array('Team'=>$Attackers, 'MemberDescription'=>'Attacker', 'MemberInputName'=>'attackers')); ?>
			</td>
		</tr>
		<tr>
			<td style="vertical-align:top">
				Defenders<br />
				<?php $this->includeTemplate('includes/CombatSimTeamDetails.inc',array('Team'=>$Defenders, 'MemberDescription'=>'Defender', 'MemberInputName'=>'defenders')); ?>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="center">
				<br />All drones, shields, armour assumed full at the start of the simulation<br /><br />
				<input type="submit" name="update" value="Update Details" />&nbsp;
				<input type="submit" name="run" value="Run Simulation" />&nbsp;
				<input type="submit" name="death_run" value="Run Simulation TO THE DEATH!!" />
			</td>
		</tr>
	</table>
</form><?php
if($TraderCombatResults)
{
	$this->includeTemplate('includes/TraderFullCombatResults.inc');
	?><br /><?php
} ?>