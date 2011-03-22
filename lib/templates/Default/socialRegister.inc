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
	<td width="135">&nbsp;</td>
	<td width="1" bgcolor="#0B8D35"></td>
	<td align="left" valign="top" width="600" bgcolor="#06240E">
		<table width="100%" height="100%" border="0" cellspacing="5" cellpadding="5">
		<tr>
			<td valign="top">

				<h1>Use Existing Login</h1>
				<form action="login_processing.php?social=1" method="POST">
					<table border="0" cellspacing="0" cellpadding="1">
						<tr>
							<td width="27%">User name:</td>
							<td width="73%"><input type="text" name="login" size="20" maxlength="32" id="InputFields"></td>
						</tr>
						<tr>
							<td width="27%">Password:</td>
							<td width="73%"><input type="password" name="password" size="20" maxlength="32" id="InputFields"></td>
						</tr>
					</table>
					<p><input type="submit" name="link_login" value="Link Login"></p>
				</form>
				<br/>

				<h1>Or Create Login</h1>
				<p align="justify">
					Creating multiple logins will not be tolerated. If we discover someone playing with several
					logins, then all of them will be deleted. We have implemented architecture elements to help
					us detect this. There will be no toleration for multis! If an player is caught using a multi
					that player's accounts WILL be deleted without ANY warning.<br />
					<a href="http://smrcnn.smrealms.de/viewtopic.php?t=382" style="font-weight:bold;">Click HERE</a> for more information.
				</p>

				<form action="login_create_processing.php?socialReg=1" method="POST">
					<div align="center" style="color:red;">*** Any personal information is confidential and will not be sold to third parties. ***</div>

					<table border="0" cellspacing="0" cellpadding="1">
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
						<tr>
							<th colspan="2">Game Information</th>
						</tr>
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
						<?php //BETA
	//					<tr>
	//						<td width="27%">Beta Key:</td>
	//						<td width="73%"><input type="text" name="beta_key" size="20" maxlength="50" id="InputFields"></td>
	//					</tr>
						?>
						<tr>
							<td width="27%">User name:</td>
							<td width="73%"><input type="text" name="login" size="20" maxlength="32" id="InputFields"></td>
						</tr>
						<tr>
							<td width="27%">Password (Optional):</td>
							<td width="73%"><input type="password" name="password" size="20" maxlength="32" id="InputFields"></td>
						</tr>
						<tr>
							<td width="27%">Verify (Optional):</td>
							<td width="73%"><input type="password" name="pass_verify" size="20" maxlength="32" id="InputFields"></td>
						</tr><?php
						if($SocialLogin->getEmail()==null)
						{ ?>
							<tr>
								<td width="27%">Email:</td>
								<td width="73%"><input type="text" name="email" size="20" maxlength="32" id="InputFields"></td>
							</tr>
							<tr>
								<td width="27%">Verify Email:</td>
								<td width="73%"><input type="text" name="email_verify" size="20" maxlength="32" id="InputFields"></td>
							</tr><?php
						} ?>
						<tr>
							<td width="27%">Local Time:</td>
							<td width="73%">
								<select name="timez" id="InputFields">
									<?php
									$time = TIME;
										for ($i = -12; $i<= 11; $i++) echo('<option value="'.$i.'">' . date(DEFAULT_DATE_TIME_SHORT, $time + $i * 3600));
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td width="27%">Referral ID (Optional):</td>
							<td width="73%"><input type="text" name="referral_id" size="10" maxlength="20" id="InputFields"<?php if(isset($_REQUEST['ref'])){ echo 'value="'.$_REQUEST['ref'].'"'; }?>></td>
						</tr>
						<tr>
							<td colspan="2">&nbsp;</td>
						</tr>
					</table>

					<p>&nbsp;</p>

					<div align="center" style="font-size=80%;">
						I have read and accepted the User Agreement above,<br />
						made sure all information submitted is correct,<br />
						and understand that my account can be closed or deleted<br />
						with no warning should it contain<br />
						invalid or false information.<br /><br />
						<input type="checkbox" name="agreement" value="checkbox">
					</div>

					<p><input type="submit" name="create_login" value="Create Login"></p>
				</form>

			</td>
		</tr>
		</table>
	</td>
	<td width="1" bgcolor="#0B8D35"></td>
	<td width="135">&nbsp;</td>
</tr>
<tr>
	<td></td>
	<td colspan="3" height="1" bgcolor="#0b8d35"></td>
	<td></td>
</tr>
</table>

</body>
</html>