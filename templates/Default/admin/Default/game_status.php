<form method="POST" action="<?php echo $ProcessingHREF; ?>">
<?php
if ($ServerIsOpen) { ?>
	If you wish to close Space Merchant Realms, please enter a reason for the closure.
	This will be displayed when players attempt to log in during the closure.<br /><br />
	<b>Reason: </b>
	<input spellcheck="true" type="text" name="close_reason" maxlength="255" size="80"><br /><br />
	<b>NOTE:</b> Closing the server will kick all players and disable general logins.
	Only admins with permission to reopen the game will be allowed to log in while closed.<br /><br />
	<input type="submit" name="action" value="Close"><?php
} else { ?>
	Do you want to reopen Space Merchant Realms?<br /><br />
	<input type="submit" name="action" value="Open"><?php
} ?>
</form>
