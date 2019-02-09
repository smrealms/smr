<a href="<?php echo $BackHREF; ?>">&lt;&lt;Back</a>

<?php

//another script for comp share
if ($type == 'comp_share') {
	$this->includeTemplate('comp_share.php');

} elseif ($type == 'list') { ?>
	<form method="POST" action="<?php echo $CloseHREF; ?>">
		<table class="standard">
			<tr>
				<th>ID</th>
				<th>Login</th>
				<th>IP</th>
				<th>Host</th>
				<th>Match?</th>
				<th>Disable?</th>
				<th>Reason</th>
				<th>Closed?</th><?php
				foreach ($Rows as $Row) { ?>
					<tr>
						<td><?php echo $Row['account_id']; ?></td>
						<td><?php echo $Row['login']; ?></td>
						<td><?php echo $Row['ip']; ?></td>
						<td><?php echo $Row['host']; ?></td><?php
						if ($Row['matches']) { ?>
							<td><span class="red">Yes</span></td>
							<td><input type=checkbox name="disable_id[]" value="<?php echo $Row['account_id']; ?>" <?php echo $Row['checked']; ?>></td>
							<td><input type=text name="suspicion[<?php echo $Row['account_id']; ?>]" value="<?php echo $Row['suspicion']; ?>" id="InputFields"></td><?php
						} else { ?>
							<td></td>
							<td><input type=checkbox name="disable_id[]" value="<?php echo $Row['account_id']; ?>"></td>
							<td><input type=text name="suspicion2[<?php echo $Row['account_id']; ?>]" id="InputFields"></td><?php
						} ?>
						<td><?php echo $Row['close_reason']; ?></td>
					</tr><?php
				} ?>
			</table>
		<input type="submit" name="action" value="Disable" />
	</form><?php

} elseif ($type == 'account_ips') { ?>
	<center><?php
		echo $Summary;
		if (!empty($Exception)) { ?>
			<br />This account has an exception: <?php echo $Exception;
		}
		if (!empty($CloseReason)) { ?>
			<br />This account is closed: <?php echo $CloseReason;
		} ?>
		<br /><br />
		<form method="POST" action="<?php echo $CloseHREF; ?>">
		<table class="standard">
			<tr>
				<th>IP</th>
				<th>Host</th>
				<th>Time</th>
			</tr><?php
			foreach ($Rows as $Row) { ?>
				<tr>
					<td><?php echo $Row['ip']; ?></td>
					<td><?php echo $Row['host']; ?></td>
					<td><?php echo $Row['date']; ?></td>
				</tr><?php
			} ?>
			</table>
			<p>
				Reason:&nbsp;<input type=text name="reason" value="Reason Here">&nbsp;
				<input type=hidden name=second value="<?php echo $BanAccountID; ?>" />
				<input type="submit" name="action" value="Disable Account" />
			</p>
		</form>
	</center><?php

} elseif (in_array($type, ['search', 'alliance_ips', 'wild_log', 'wild_in',
                           'compare', 'compare_log', 'wild_ip', 'wild_host'])) { ?>

	<center>
		<?php echo $Summary; ?><br /><br />
		<form method="POST" action="<?php echo $CloseHREF; ?>">
			<table class="standard">
				<tr>
					<th>Account ID</th>
					<th>Login</th>
					<th>Time</th>
					<th>IP</th>
					<th>Host</th>
					<th>Player Names</th>
					<th>Disable</th>
					<th>Exception?</th>
					<th>Closed?</th>
				</tr><?php
				foreach ($Rows as $Row) { ?>
					<tr>
						<td><?php echo $Row['account_id']; ?></td>
						<td><?php echo $Row['login']; ?></td>
						<td><?php echo $Row['date']; ?></td>
						<td><?php echo $Row['ip']; ?></td>
						<td><?php echo $Row['host']; ?></td>
						<td><?php echo $Row['names']; ?></td>
						<td><input type=checkbox name=same_ip[] value="<?php echo $Row['account_id']; ?>"></td>
						<td><?php echo $Row['exception']; ?></td>
						<td><?php echo $Row['close_reason']; ?></td>
					</tr><?php
				} ?>
			</table>
			<p><input type="submit" name="action" value="Disable Accounts" /></p>
			<input type=hidden name=first value="first">
		</form>
	</center><?php

} ?>
