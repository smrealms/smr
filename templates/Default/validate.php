<form name="FORM" method="POST" action="<?php echo $ValidateFormHref ?>">

<?php
if(isset($Message)) {
	echo $Message; ?><br /><?php
} ?>

<p>
	Thank you for trying out Space Merchant Realms! We hope that you are enjoying the game. However,
	in order for you to experience the full features of the game, you need to validate your e-mail address.
	When you first created your account, a validation code was sent to: <b><?php echo $ThisAccount->getEmail(); ?></b>.
</p>
<p>
	If this address is incorrect, please edit it in your <a href="<?php echo $PreferencesLink; ?>"><u>Preferences</u></a> and a new validation code will be sent to you.
	Otherwise, if you have not received an e-mail yet, please contact an admin for help.
</p>
<p>
	The following restrictions are placed on users who have not validated their account:
</p>
<ul>
	<li>No additional turns are granted to your traders while you are not validated.</li>
	<li>Bank access is denied.</li>
	<li>You will be unable to land on a planet.</li>
	<li>You will be unable to access alliances.</li>
	<li>You will be unable to vote in the daily politics of the universe.</li>
</ul>
<p>
	Enter validation code:&nbsp;&nbsp;
	<input type="text" name="validation_code" maxlength="10" size="10" class="InputFields center">
</p>
<p align="center">
	<input type="submit" name="action" value="Validate me now!" class="InputFields">
	&nbsp;&nbsp;
	<input type="submit" name="action" value="I'll validate later." class="InputFields">
</p>
</form>
