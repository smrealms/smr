<table class="standard center">
	<tr>
		<th>Player Name</th>
		<th>Bounty Amount (Credits)</th>
		<th>Bounty Amount (SMR credits)</th>
	</tr>
	<?php
	foreach ($Bounties as $Bounty) { ?>
		<tr>
			<td><?php echo $Bounty['player']->getLinkedDisplayName(); ?></td>
			<td><span class="creds"><?php echo number_format($Bounty['credits']); ?></span></td>
			<td><span class="red"><?php echo number_format($Bounty['smr_credits']); ?></span></td>
		</tr><?php
	} ?>
</table>
<p>&nbsp;</p>
