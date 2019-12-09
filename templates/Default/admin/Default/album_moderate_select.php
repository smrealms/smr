<?php
if (empty($Approved)) { ?>
	<p>There are no entries that can be moderated at this time.</p><?php
} else { ?>
	<p>Select the entry you wish to edit:</p>

	<form method="POST" action="<?php echo $ModerateHREF; ?>">
		<select class="InputFields" name="account_id"><?php
			foreach ($Approved as $AccountID => $Name) { ?>
				<option value="<?php echo $AccountID; ?>"><?php echo $Name; ?></option><?php
			} ?>
		</select>
		&nbsp;
		<input type="submit" value="Submit" />
	</form><?php
}
