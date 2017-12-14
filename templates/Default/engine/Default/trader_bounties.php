You have the following bounties on your head:<br /><br />
<table>
	<tr>
		<td><span class="green">Federal</span> :</td>
		<td><?php echo $BountyHQ; ?></td>
	</tr>
	<tr>
		<td><span class="red">Underground</span> :</td>
		<td><?php echo $BountyUG; ?></td>
	</tr>
</table>
<br />

<p>Bounties awaiting your collection:</p>

<table class="standard fullwidth">
	<tr>
		<th>Federal</th>
		<th>Underground</th>
	</tr>

	<tr><?php
		// AllClaims is array(ClaimsHQ, ClaimsUG)
		foreach ($AllClaims as $Claims) { ?>
			<td style="width:50%" class="top"><?php
				if (empty($Claims)) {
					echo "None";
				}
				foreach ($Claims as $Claim) {
					echo $Claim['player']->getLinkedDisplayName(); ?> : <span class="creds"><? echo number_format($Claim['credits']); ?></span> credits and <span class="yellow"><?php echo number_format($Claim['smr_credits']); ?></span> SMR credits<br />
					<?php
				} ?>
			</td><?php
		} ?>
	</tr>
</table>
