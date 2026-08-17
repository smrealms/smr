<?php declare(strict_types=1);

/**
 * @var array<Smr\Bounty> $Bounties
 */

?>
<table class="standard center">
	<tr>
		<th>Player Name</th>
		<th>Bounty Amount (Credits)</th>
		<th>Bounty Amount (SMR credits)</th>
	</tr>
	<?php
	foreach ($Bounties as $Bounty) { ?>
		<tr>
			<td><?php echo $Bounty->getTargetPlayer()->getLinkedDisplayName(); ?></td>
			<td><span class="creds"><?php echo number_format($Bounty->getCredits()); ?></span></td>
			<td><span class="red"><?php echo number_format($Bounty->getSmrCredits()); ?></span></td>
		</tr><?php
	} ?>
</table>
<p>&nbsp;</p>
