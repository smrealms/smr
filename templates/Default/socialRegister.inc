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
	<td width="135">&nbsp;</td>
	<td width="1" bgcolor="#0B8D35"></td>
	<td align="left" valign="top" width="600" bgcolor="#06240E">
		<table width="100%" height="100%" border="0" cellspacing="5" cellpadding="5">
		<tr>
			<td valign="top">

				<h1>Link To Existing Login</h1>
				<form action="login_processing.php?social=1" method="POST">
					<table border="0" cellspacing="0" cellpadding="1">
						<tr>
							<td width="27%">User name:</td>
							<td width="73%"><input type="text" name="login" size="20" maxlength="32" class="InputFields" value="<?php if (isset($MatchingLogin)) { echo $MatchingLogin; } ?>"></td>
						</tr>
						<tr>
							<td width="27%">Password:</td>
							<td width="73%"><input type="password" name="password" size="20" maxlength="32" class="InputFields"></td>
						</tr>
					</table>
					<p><input type="submit" name="link_login" value="Link Login"></p>
				</form>
				<br/>

				<?php
				if (!isset($MatchingLogin)) { ?>
					<h1>Or Create New Login</h1>
					<p align='justify'>
						Creating multiple logins is not allowed.
						<a href='https://wiki.smrealms.de/rules' target="_blank" style='font-weight:bold;'>Click HERE</a> for more information.
					</p>


					<p class="red">Personal information is confidential and will not be sold to third parties.</p>
					<form action="login_create_processing.php?socialReg=1" method="POST">

						<table border="0" cellspacing="0" cellpadding="1">
							<tr>
								<td width="27%">User name:</td>
								<td width="73%"><input type="text" name="login" size="20" maxlength="32" class="InputFields"></td>
							</tr>
						<tr>
							<td width="27%">Password (Optional):</td>
							<td width="73%"><input type="password" name="password" size="20" maxlength="32" class="InputFields"></td>
							</tr>
							<tr>
								<td width="27%">Verify (Optional):</td>
								<td width="73%"><input type="password" name="pass_verify" size="20" maxlength="32" class="InputFields"></td>
							</tr><?php
							if (empty($SocialLogin->getEmail())) { ?>
								<tr>
									<td width="27%">Email:</td>
									<td width="73%"><input type="email" name="email" size="20" maxlength="32" class="InputFields"></td>
								</tr>
								<tr>
									<td width="27%">Verify Email:</td>
									<td width="73%"><input type="email" name="email_verify" size="20" maxlength="32" class="InputFields"></td>
								</tr><?php
							} ?>
							<tr>
								<td width="27%">Local Time:</td>
								<td width="73%">
									<select name="timez" class="InputFields">
										<?php
										$time = TIME;
											for ($i = -12; $i<= 11; $i++) echo('<option value="'.$i.'">' . date(DEFAULT_DATE_TIME_SHORT, $time + $i * 3600));
										?>
									</select>
								</td>
							</tr>
							<tr>
								<td width="27%">Referral ID (Optional):</td>
								<td width="73%"><input type="number" name="referral_id" size="10" maxlength="20" class="InputFields"<?php if(isset($_REQUEST['ref'])){ echo 'value="'.htmlspecialchars($_REQUEST['ref']).'"'; }?>></td>
							</tr>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
						</table>

						<div style='font-size:80%;'>
							<input type='checkbox' name='agreement' value='checkbox'>
							I have read and accept the
							<a href='https://wiki.smrealms.de/rules' target="_blank" style='font-weight:bold;'>Terms of Use</a>.
						</div>

						<p><input type="submit" name="create_login" value="Create Login"></p>
					</form><?php
				} ?>

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
