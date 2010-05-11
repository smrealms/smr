<?php require_once('config.inc'); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
            "http://www.w3.org/TR/html4/loose.dtd">

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

								<h1>FORGOT YOUR PASSWORD?</h1>

								For security reasons, please enter the Username and Email that your account is registered to:

								<form action="resend_password_processing.php" method="POST">
										<div align="center">
												<table border="0">
													<tr>
															<th align="right">Username:</th>
															<td><input name="login" id="InputFields"></td>
													</tr>
													<tr>
															<th align="right">Email:</th>
															<td><input name="email" id="InputFields"></td>
													</tr>
												</table>
												<p><input type="submit" value="Resend my password" id="InputFields"></p>
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