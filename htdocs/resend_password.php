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
				<td valign="top" width="600" bgcolor="#06240E">
					<table width="100%" height="100%" border="0" cellspacing="5" cellpadding="5">
						<tr>
							<td valign="top">

								<h1>FORGOT YOUR PASSWORD?</h1>

								Please enter the e-mail address associated with your account:
								<br /><br />

								<form action="resend_password_processing.php" method="POST">
									<div class="center">
										<table class="center" border="0">
											<tr>
												<th class="right">Email:</th>
												<td><input required type="email" name="email" class="InputFields"></td>
											</tr>
										</table>
										<p><input type="submit" value="Resend my password" class="InputFields"></p>
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
