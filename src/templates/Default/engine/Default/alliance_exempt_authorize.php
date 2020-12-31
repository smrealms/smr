<h2>Exemption Requests</h2>
<br /><?php
if (!empty($Transactions)) { ?>
	Alliance members have requested exemptions for the following transactions.<br /><br />
	<form method="POST" action="<?php echo $ExemptHREF; ?>">
		<table class="standard">
			<tr>
				<th>Player Name</th>
				<th>Type</th>
				<th>Reason</th>
				<th>Amount</th>
				<th>Approve</th>
			</tr><?php
			foreach ($Transactions as $Trans) { ?>
				<tr>
					<td><?php echo $Trans['player']; ?></td>
					<td><?php echo $Trans['type']; ?></td>
					<td><?php echo htmlentities($Trans['reason']); ?></td>
					<td><?php echo $Trans['amount']; ?></td>
					<td><input type="checkbox" name="exempt[<?php echo $Trans['transactionID']; ?>]"></td>
				</tr><?php
			} ?>
		</table>
		<br />
		<input type="submit" name="action" value="Make Exempt" />
	</form><?php
} else { ?>
	Nothing to authorize.<?php
} ?>
