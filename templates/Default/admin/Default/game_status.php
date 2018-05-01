<form method="POST" action="<?php echo $ProcessingHREF; ?>">
<?php
if ($ServerIsOpen) { ?>
	If you wish to close Space Merchant Realms, please enter a reason for the closure.<br /><br />
	<input spellcheck="true" type="text" name="close_reason" maxlength="255" size="100" id="InputFields"><br /><br />
	<b>NOTE:</b> Closing the server will kick all players and disable general logins.
	Only admins with permission to reopen the game will be allowed to log in while closed.<br /><br />
	<input type="submit" name="action" value="Close"><?php
} else { ?>
	Do you want to reopen Space Merchant Realms?<br /><br />
	<input type="submit" name="action" value="Open"><?php
} ?>
</form>
