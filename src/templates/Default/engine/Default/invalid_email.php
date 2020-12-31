<p>Your account was closed because we detected that your e-mail address is
invalid. To re-open your account, please either re-validate your current
address, or change your address. A new validation code will be sent right
away!</p>

<p><b>Current e-mail address:</b> <?php echo $ThisAccount->getEmail(); ?></p>
<br />

<form method="POST" action="<?php echo $ReopenLink; ?>">
	<h2>Re-validate current address</h2>
	<p>
		If you believe that your current e-mail address is correct, you can simply
		re-validate this address.
	</p>

	<p><input type="submit" name="action" value="Resend Validation Code" /></p>
	<br />

	<h2>Enter new address</h2>
	<p>If your current address is no longer valid, please enter a new one.</p>
	<p>New e-mail address: <input type="email" name="email" size="40" maxlength="128"></p>

	<p><input type="submit" name="action" value="Change E-mail Address"></p>
</form>
