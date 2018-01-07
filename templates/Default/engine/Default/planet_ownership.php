<?php

if (!$Planet->hasOwner()) { ?>
	<p>The planet is unclaimed.</p>
	<form method="POST" action="<?php echo $ProcessingHREF; ?>">
		<input type="submit" name="action" value="Take Ownership" id="InputFields" />
	</form><?php
}
else {
	if ($Planet->getOwnerID() != $Player->getAccountID()) { ?>
		<p>You can claim the planet when you enter the correct password.</p>
		<form method="POST" action="<?php echo $ProcessingHREF; ?>">
			<input type="text" name="password" id="InputFields">&nbsp;&nbsp;&nbsp;
			<input type="submit" name="action" value="Take Ownership" id="InputFields" />
		</form><?php
	}
	else { ?>
		<p>You can set a password for the planet.</p>
		<form method="POST" action="<?php echo $ProcessingHREF; ?>">
			<input type="text" name="password" value="<?php echo htmlspecialchars($Planet->getPassword()); ?>" id="InputFields" />&nbsp;&nbsp;&nbsp;
			<input type="submit" name="action" value="Set Password" id="InputFields" />
		</form>
		<br />

		<p>You can rename the planet.</p>
		<form method="POST" action="<?php echo $ProcessingHREF; ?>">
			<input type="text" name="name" value="<?php echo htmlspecialchars($Planet->getName()); ?>" id="InputFields" />&nbsp;&nbsp;&nbsp;
			<input type="submit" name="action" value="Rename" id="InputFields" />
		</form><?php
	}
}

?>
