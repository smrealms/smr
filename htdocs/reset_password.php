<?php require_once('config.inc'); ?>
<!DOCTYPE html>

<html>
	<head>
		<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS; ?>">
		<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS_COLOUR; ?>">
		<title>Space Merchant Realms</title>
	</head>

	<body>

		<table cellspacing="0" cellpadding="0" border="0" width="100%" height="100%">
			<tr>
				<td></td>
				<td colspan="3" height="1" bgcolor="#0B8D35"></td>
				<td></td>
			</tr>
			<tr>
				<td width="100">&nbsp;</td>
				<td width="1" bgcolor="#0B8D35"></td>
				<td align="left" valign="top" width="600" bgcolor="#06240E">
					<table width="100%" height="100%" border="0" cellspacing="5" cellpadding="5">
					<tr>
						<td valign="top">

							<h1>RESET PASSWORD</h1>

							For security reasons, please enter your username and the password reset code you requested:

							<form action="reset_password_processing.php" method="POST">
									<div align="center">
											<table border="0">
												<tr>
														<th align="right">Username:</th>
														<td><input name="login" type="text" class="InputFields" value="<?php echo isset($_REQUEST['login']) ? htmlspecialchars($_REQUEST['login']) : ''; ?>" /></td>
												</tr>
												<tr>
														<th align="right">Password Reset Code:</th>
														<td><input name="password_reset" type="text" class="InputFields" value="<?php echo isset($_REQUEST['resetcode']) ? htmlspecialchars($_REQUEST['resetcode']) : ''; ?>" /></td>
												</tr>
												<tr>
														<th align="right">New Password:</th>
														<td><input name="password" type="password" class="InputFields" /></td>
												</tr>
												<tr>
														<th align="right">Verify New Password:</th>
														<td><input name="pass_verify" type="password" class="InputFields" /></td>
												</tr>
											</table>
											<p><input type="submit" value="Reset my password" class="InputFields" /></p>
									</div>
							</form>

						</td>
					</tr>
					</table>
				</td>
				<td width="1" bgcolor="#0B8D35"></td>
				<td width="100">&nbsp;</td>
			</tr>
			<tr>
				<td></td>
				<td colspan="3" height="1" bgcolor="#0b8d35"></td>
				<td></td>
			</tr>
		</table>

	</body>
</html>
