<p>
	Choose the log files you wish to view or delete!<br />
	Don't keep unnecessary data!
</p><?php

if (count($LoggedAccounts)>0) { ?>
	<form method="POST" action="<?php echo $LogConsoleFormHREF; ?>"><?php

		// put hidden fields in for log type to have all fields selected on next page.
		foreach($LogTypes as $LogType) { ?>
			<input type="hidden" name="log_type_ids[<?php echo $LogType; ?>]" value="1"><?php
		} ?>
		
		<table class="standard">
			<tr>
				<th>Login</th>
				<th>Entries</th>
				<th>Action</th>
				<th>Notes</th>
			</tr><?php
	
			foreach($LoggedAccounts as $LoggedAccount) { ?>
				<tr>
					<td valign="top"><?php echo $LoggedAccount['Login']; ?></td>
					<td valign="top" class="center"><?php echo $LoggedAccount['TotalEntries']; ?></td>
					<td valign="middle" class="center"><input type="checkbox" name="account_ids[]" value="<?php echo $LoggedAccount['AccountID']; ?>"<?php if($LoggedAccount['Checked']){ ?> checked="checked"<?php } ?>></td>
					<td><?php echo $LoggedAccount['Notes']; ?></td>
				</tr><?php
			} ?>
		
			<tr>
				<td colspan="3">&nbsp;</td>
				<td>
					<input type="submit" name="action" value="View" class="InputFields" />	&nbsp;&nbsp;<input type="submit" name="action" value="Delete" class="InputFields" />
				</td>
			</tr>
		</table>

	</form>

	<p>&nbsp;</p>

	<p>Check for:</p>
	<ul>
		<li><a href="<?php echo $AnonAccessHREF; ?>">Anonymous Account access</a></li>
	</ul><?php
}
else { ?>
	There are no log entries at all!<?php
}
?>
