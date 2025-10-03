<?php declare(strict_types=1);

use Smr\Epoch;
use Smr\Request;

?>
<div class="centered" style="width: 630px;">
	<h1>Link To Existing Login</h1>
	<form action="login_processing.php?social=1" method="POST">
		<table border="0" cellspacing="0" cellpadding="1">
			<tr>
				<td width="27%">User name:</td>
				<td width="73%"><input required type="text" name="login" size="20" maxlength="32" class="InputFields" value="<?php if (isset($MatchingLogin)) { echo $MatchingLogin; } ?>"></td>
			</tr>
			<tr>
				<td width="27%">Password:</td>
				<td width="73%"><input required type="password" name="password" size="20" maxlength="32" class="InputFields"></td>
			</tr>
		</table>
		<p><?php echo create_submit('link_login', 'Link Login', fields: ['class' => 'InputFields']); ?></p>
	</form>
	<br/>

	<?php
	if (!isset($MatchingLogin)) { ?>
		<h1>Or Create New Login</h1>

		<div class="register-note">
			<p><b>Important Information:</b></p>
			<ul>
				<li>
					Creating multiple logins is not allowed.
					<a href="<?php echo WIKI_URL; ?>/rules" target="_blank">
						<img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Terms of Use" />
					</a>
				</li>
				<li>
					Personal information is confidential and will not be sold to third parties.
					<a href="<?php echo WIKI_URL; ?>/privacy" target="_blank">
						<img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Privacy Policy" />
					</a>
				</li>
			</ul>
		</div>
		<br />

		<form action="login_create_processing.php?socialReg=1" method="POST">

			<table border="0" cellspacing="0" cellpadding="6">
				<tr>
					<td width="27%">User name:</td>
					<td width="73%"><input required type="text" name="login" size="20" maxlength="32" class="InputFields"></td>
				</tr>
			<tr>
				<td width="27%">Password (Optional):</td>
				<td width="73%"><input type="password" name="password" size="20" maxlength="32" class="InputFields"></td>
				</tr>
				<tr>
					<td width="27%">Verify Password (Optional):</td>
					<td width="73%"><input type="password" name="pass_verify" size="20" maxlength="32" class="InputFields"></td>
				</tr>
				<tr>
					<td width="27%">Local Time:</td>
					<td width="73%">
						<select name="timez" class="InputFields">
							<?php
							$time = Epoch::time();
								for ($i = -12; $i <= 11; $i++) {
									echo('<option value="' . $i . '">' . date(DEFAULT_TIME_FORMAT, $time + $i * 3600));
								}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td width="27%">Referral ID (Optional):</td>
					<td width="73%"><input type="number" name="referral_id" size="10" maxlength="20" class="InputFields"<?php if (Request::has('ref')) { echo 'value="' . Request::getInt('ref') . '"'; }?>></td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
			</table>

			<div style='font-size:80%;'>
				<input required type='checkbox' name='agreement' value='checkbox'>
				I have read and accept the
				<a href="<?php echo WIKI_URL; ?>/rules" target="_blank" style="font-weight:bold;">Terms of Use</a>
				and
				<a href="<?php echo WIKI_URL; ?>/privacy" target="_blank" style="font-weight:bold;">Privacy Policy</a>.
			</div>

			<p><?php echo create_submit('create_login', 'Create Login', fields: ['class' => 'InputFields']); ?></p>
		</form><?php
	} ?>
</div>
