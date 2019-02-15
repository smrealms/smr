<?php
if (empty($Rows)) {
	if (isset($Message)) { ?>
		<p><span class="red"><?php echo $Message; ?></span></p><?php
	} ?>
	<p>What account would you like to view?</p>
	<form method="POST" action="<?php echo $AnonViewHREF; ?>">
		<p>Anon Account ID: <input type="number" name="anon_account" /></p>
		<p>Game ID: <input type="number" name="view_game_id" /></p>
		<input type="submit" name="action" value="Continue" />
	</form><?php
} else { ?>
	<a href="<?php echo $AnonViewHREF; ?>">&lt;&lt;Back</a>
		<p>Transactions from Anonymous Account #<?php echo $AnonID; ?> in Game <?php echo $ViewGameID; ?></p>
		<table class="standard">
			<tr>
				<th>Player Name</th>
				<th>Type</th>
				<th>Amount</th></tr>
		<?php
		foreach ($Rows as $Row) { ?>
			<tr>
				<td><?php echo $Row['player_name']; ?></td>
				<td><?php echo $Row['transaction']; ?></td>
				<td class="right"><?php echo number_format($Row['amount']); ?></td>
			</tr><?php
		} ?>
		</table><?php
} ?>
