<?php declare(strict_types=1);

/**
 * @var array<int, string> $Approved
 */

if (count($Approved) === 0) { ?>
	<p>There are no entries that can be moderated at this time.</p><?php
} else { ?>
	<p>Select the entry you wish to edit:</p>

	<form method="POST" action="<?php echo $ModerateHREF; ?>">
		<select name="account_id"><?php
			foreach ($Approved as $AccountID => $Name) { ?>
				<option value="<?php echo $AccountID; ?>"><?php echo $Name; ?></option><?php
			} ?>
		</select>
		&nbsp;
		<?php echo create_submit_display('Submit'); ?>
	</form><?php
}
