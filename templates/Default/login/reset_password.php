<div class="center centered">
	<h1>Password Reset</h1>

	For security reasons, please enter your username and the password reset code you received.

	<form action="reset_password_processing.php" method="POST">
			<div class="center">
					<table class="center" border="0">
						<tr>
								<th class="right">Username:</th>
								<td><input required name="login" type="text" class="InputFields" value="<?php echo isset($_REQUEST['login']) ? htmlspecialchars($_REQUEST['login']) : ''; ?>" /></td>
						</tr>
						<tr>
								<th class="right">Password Reset Code:</th>
								<td><input required name="password_reset" type="text" class="InputFields" value="<?php echo isset($_REQUEST['resetcode']) ? htmlspecialchars($_REQUEST['resetcode']) : ''; ?>" /></td>
						</tr>
						<tr>
								<th class="right">New Password:</th>
								<td><input required name="password" type="password" class="InputFields" /></td>
						</tr>
						<tr>
								<th class="right">Verify New Password:</th>
								<td><input required name="pass_verify" type="password" class="InputFields" /></td>
						</tr>
					</table>
					<p><input type="submit" value="Reset my password" class="InputFields" /></p>
			</div>
	</form>
</div>
