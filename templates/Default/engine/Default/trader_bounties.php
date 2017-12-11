
Bounties awaiting collection:<br /><br />

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
					echo $Claim['name']; ?> : <span class="creds"><? echo $Claim['credits']; ?></span> credits and <span class="yellow"><?php echo $Claim['smr_credits']; ?></span> SMR credits<br />
					<?php
				} ?>
			</td><?php
		} ?>
	</tr>
</table>
