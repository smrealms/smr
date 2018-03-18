<?php
require_once('config.inc');
?>
<!DOCTYPE html>

<html>
<head>
	<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS; ?>">
	<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS_COLOUR; ?>">
	<style>.recaptchatable #recaptcha_response_field {background:white; color: black;}</style>
	<title>Space Merchant Realms</title>
	<script src='//www.google.com/recaptcha/api.js'></script>
</head>

<body>

<table cellspacing='0' cellpadding='0' border='0' width='100%' height='100%'>
<tr>
	<td></td>
	<td colspan='3' height='1' bgcolor='#0B8D35'></td>
	<td></td>
</tr>
<tr>
	<td width='135'>&nbsp;</td>
	<td width='1' bgcolor='#0B8D35'></td>
	<td align='left' valign='top' width='600' bgcolor='#06240E'>
		<table width='100%' height='100%' border='0' cellspacing='5' cellpadding='5'>
		<tr>
			<td valign='top'>

				<h1>Create Login</h1>
				<p align='justify'>
					Creating multiple logins is not allowed.
				</p>

				<form action='login_create_processing.php' method='POST'>
					<div align='center' style='color:red;'>*** Any personal information is confidential and will not be sold to third parties. ***</div>

					<table border='0' cellspacing='0' cellpadding='1'>
					<tr>
						<td colspan='2'>&nbsp;</td>
					</tr>
					<tr>
						<th colspan='2'>Game Information</th>
					</tr>
					<tr>
						<td colspan='2'>&nbsp;</td>
					</tr>
					<tr>
						<td width='27%'>User name:</td>
						<td width='73%'><input type='text' name='login' size='20' maxlength='32' id='InputFields'></td>
					</tr>
					<tr>
						<td width='27%'>Password:</td>
						<td width='73%'><input type='password' name='password' size='20' maxlength='32' id='InputFields'></td>
					</tr>
					<tr>
						<td width='27%'>Verify Password:</td>
						<td width='73%'><input type='password' name='pass_verify' size='20' maxlength='32' id='InputFields'></td>
					</tr>
					<tr>
						<td width='27%'>E-Mail Address:</td>
						<td width='73%'><input type='email' name='email' size='50' maxlength='128' id='InputFields'></td>
					</tr>
					<tr>
						<td width='27%'>Verify E-Mail Address:</td>
						<td width='73%'><input type='email' name='email_verify' size='50' maxlength='128' id='InputFields'></td>
					</tr>
					<tr>
						<td width='27%'>Local Time:</td>
						<td width='73%'>
							<select name="timez" id="InputFields"><?php
								$time = TIME;
								for ($i = -12; $i<= 11; $i++) {
									?><option value="<?php echo $i; ?>"><?php echo date(DEFAULT_DATE_TIME_SHORT, $time + $i * 3600); ?></option><?php
								} ?>
							</select>
						</td>
					</tr>
					<tr>
						<td width='27%'>Referral ID (Optional):</td>
						<td width='73%'><input type='text' name='referral_id' size='10' maxlength='20' id='InputFields'<?php if(isset($_REQUEST['ref'])){ echo 'value="'.htmlspecialchars($_REQUEST['ref']).'"'; }?>></td>
					</tr>
					<tr>
						<td colspan='2'>&nbsp;</td>
					</tr>
					<tr>
						<th colspan='2'>User Information</th>
					</tr>
					<tr>
						<td colspan='2'>&nbsp;</td>
					</tr>
					<tr>
						<td width='27%'>First Name:</td>
						<td width='73%'><input type='text' name='first_name' size='20' maxlength='50' id='InputFields'></td>
					</tr>
					<tr>
						<td width='27%'>Last Name:</td>
						<td width='73%'><input type='text' name='last_name' size='20' maxlength='50' id='InputFields'></td>
					</tr>
					<tr>
						<td colspan='2'><div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_PUBLIC; ?>"></div></td>
					</tr>
					</table>

					<p>&nbsp;</p>

					<div align='center' style='font-size:80%;'>
						I have read and accept the
						<a href='https://wiki.smrealms.de/rules' target="_blank" style='font-weight:bold;'>User Agreement</a>.<br />
						I understand that my account can be closed or deleted<br />
						without warning should it contain invalid or false information.<br /><br />
						<input type='checkbox' name='agreement' value='checkbox'>
					</div>

					<p><input type='submit' name='create_login' value='Create Login'></p>
				</form>

			</td>
		</tr>
		</table>
	</td>
	<td width='1' bgcolor='#0B8D35'></td>
	<td width='135'>&nbsp;</td>
</tr>
<tr>
	<td></td>
	<td colspan='3' height='1' bgcolor='#0b8d35'></td>
	<td></td>
</tr>
</table>

</body>
</html>
