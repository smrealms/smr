<a href="<?php echo $BackHREF; ?>"><b>&lt; Back</b></a>

<?php
if (!$AnonLogs) { ?>
	<p>None of the entries in all the log files contains anonymous bank transaction!</p><?php
	return;
} ?>

<p>The following anonymous bank accounts were accessed by logged players:</p>

<?php
foreach ($AnonLogs as $gameID => $AnonAccounts) { ?>
	<h2>Game <?php echo $gameID; ?></h2><?php
	foreach ($AnonAccounts as $anonID => $logs) { ?>
		<table>
			<tr>
				<th colspan="4">Anon Account #<?php echo $anonID; ?></th>
			</tr><?php
			foreach ($logs as $log) { ?>
				<tr>
					<td><?php echo $log['date']; ?></td>
					<td><?php echo $log['login']; ?></td>
					<td style="color:<?php echo $log['color']; ?>"><?php echo $log['type']; ?></td>
					<td><?php echo $log['amount']; ?> credits</td>
				</tr><?php
			} ?>
		</table><br /><?php
	}
} ?>
