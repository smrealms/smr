<div class="centered" style="width: 630px;">
	<h1>Create Login</h1>
	<p>
		Creating multiple logins is not allowed.
		<a href='https://wiki.smrealms.de/rules' target="_blank" style='font-weight:bold;'>Click HERE</a> for more information.
	</p>

	<p class="red">Personal information is confidential and will not be sold to third parties.</p>
	<form action='login_create_processing.php' method='POST'>

		<table border='0' cellspacing='0' cellpadding='1'>
			<tr>
				<td width='27%'>User name:</td>
				<td width='73%'><input required type='text' name='login' size='20' maxlength='32' class="InputFields"></td>
			</tr>
			<tr>
				<td width='27%'>Password:</td>
				<td width='73%'><input required type='password' name='password' size='20' maxlength='32' class="InputFields"></td>
			</tr>
			<tr>
				<td width='27%'>Verify Password:</td>
				<td width='73%'><input required type='password' name='pass_verify' size='20' maxlength='32' class="InputFields"></td>
			</tr>
			<tr>
				<td width='27%'>E-Mail Address:</td>
				<td width='73%'><input required type='email' name='email' size='50' maxlength='128' class="InputFields"></td>
			</tr>
			<tr>
				<td width='27%'>Local Time:</td>
				<td width='73%'>
					<select name="timez" class="InputFields"><?php
						$time = TIME;
						for ($i = -12; $i <= 11; $i++) {
							?><option value="<?php echo $i; ?>"><?php echo date(DEFAULT_DATE_TIME_SHORT, $time + $i * 3600); ?></option><?php
						} ?>
					</select>
				</td>
			</tr>
			<tr>
				<td width='27%'>Referral ID (Optional):</td>
				<td width='73%'><input type='number' name='referral_id' size='10' maxlength='20' class="InputFields" <?php if (isset($_REQUEST['ref'])) { echo 'value="' . htmlspecialchars($_REQUEST['ref']) . '"'; }?>></td>
			</tr>
		</table>
		<br />

		<div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_PUBLIC; ?>"></div>

		<div style='font-size:80%;'>
			<input required type='checkbox' name='agreement' value='checkbox'>
			I have read and accept the
			<a href='https://wiki.smrealms.de/rules' target="_blank" style='font-weight:bold;'>Terms of Use</a>.
		</div>

		<p><input class="InputFields" type='submit' name='create_login' value='Create Login'></p>
	</form>
</div>

<script src='//www.google.com/recaptcha/api.js'></script>
