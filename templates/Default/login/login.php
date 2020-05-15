<table class="center nobord" style="width:690px; border-spacing:30px;">
	<tr>
		<td>
			<form action="login_processing.php" method="post">
				<table class="collapsed center">
					<tr>
						<td colspan="3">
							<img src="images/login/login_top.png" width="258" height="43" alt="">
						</td>
					</tr>
					<tr>
						<td>
							<img src="images/login/loginPw.png" width="104" height="48" alt="Login/Password" />
						</td>
						<td>
							<input id="login_username" class="InputFields inputbox uncollapse" type="text" name="login" required><br />
							<input id="login_password" class="InputFields inputbox uncollapse" type="password" name="password" required>
						</td>
						<td>
							<img src="images/login/loginPwRight.png" width="29" height="48"  alt="">
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<img src="images/login/loginPwMid.png" width="258" height="15"  alt="">
						</td>
					</tr>
				</table>
				<table class="collapsed center">
					<tr>
						<td>
							<img src="images/login/regLeft.png" width="30" height="11"  alt="">
							<a href="login_create.php">
								<img src="images/login/register.png" width="83" height="11" alt="Register">
							</a>
							<a href="resend_password.php">
								<img src="images/login/pw_reset.png" width="127" height="11" alt="Reset Password">
							</a>
							<img src="images/login/regRight.png" width="18" height="11" alt="">
						</td>
					</tr>
					<tr>
						<td>
							<img src="images/login/loginMid.png" width="258" height="24" alt="">
						</td>
					</tr>
					<tr>
						<td>
							<img src="images/login/enter_left.png" width="79" height="27" alt=""><input id="login_submit" type="image" src="images/login/enter.png" width="110" height="27" alt="Enter"><img src="images/login/enter_right.png" width="69" height="27" alt="">
						</td>
					</tr>
					<tr>
						<td>
							<img src="images/login/enter_bottom.png" width="258" height="8" alt="">
						</td>
					</tr>
				</table>
			</form>

			<br />
			<span style="font-size: 14px;">Or register &amp; login with:</span>
			<br />
			<span>
				<a class="btn-social" href="login_social_processing.php?type=<?php echo SocialLogins\Facebook::getLoginType(); ?>">
					<img alt="Facebook" src="images/login/facebook.svg" width="32" height="32">
				</a>
				<a class="btn-social" href="login_social_processing.php?type=<?php echo SocialLogins\Twitter::getLoginType(); ?>">
					<img alt="Twitter" src="images/login/twitter.svg" width="32" height="32">
				</a>
			</span>
		</td>
		<td id="twitter-feed" class="hide" style="height:256px;width:90%;">
			<a class="twitter-timeline" data-dnt="true" data-height="250" data-theme="dark" data-chrome="transparent nofooter noheader noborders" href="https://twitter.com/SMRealms?ref_src=twsrc%5Etfw"></a>
		</td>
	</tr>
</table><?php

if (!empty($GameNews)) { ?>
	<table class="standard center" style="width:730px;">
		<tr>
			<th class="shrink">Time</th>
			<th>Recent News</th>
		</tr><?php
		foreach ($GameNews as $News) { ?>
			<tr>
				<td class="center noWrap"><?php echo $News['Time']; ?></td>
				<td><?php echo $News['Message']; ?></td>
			</tr><?php
		} ?>
	</table><?php
} ?>

<br /><?php

if (isset($Story)) { ?>
	<table class="center nobord" style="width:640px;">
		<tr>
			<td><?php
				foreach ($Story as $StoryPart) { ?>
					<p style="text-align:justify">
						<span class="small" style="font-family: Verdana, Arial, Helvetica; color: #FFFFFF"><?php
								echo $StoryPart; ?>
						</span>
					</p><?php
				} ?>
			</td>
		</tr>
	</table><?php
} ?>
<br /><br />
<div class="center">
	<span class="small"><a href="imprint.html">[Imprint]</a></span>
</div>

<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="js/login.js"></script>
