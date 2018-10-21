<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $Title; ?></title>
		<link rel="stylesheet" href="css/login.css" />
		<!--[if IE]>
		<style>
		input.inputbox {
			width:121px;
			height:18px;
		}
		</style>
		<![endif]-->
	</head>

	<body>
		<div class="center">
			<p class="collapsed">
				<img src="images/login/smr_banner_16.png" width="769" height="132" alt=""><br />
				<img src="images/login/bottom_left.gif" width="7" height="11" alt="">

				<!-- small header menu -->
				<a href=""><img src="images/login/home.png" width="55" height="11" alt="Home"></a>
				<img src="images/login/site_map.png" width="87" height="11" alt="Site Map">
				<a href="mailto:support@smrealms.de" target="_blank"><img src="images/login/contact.png" width="80" height="11" alt="Contact"></a>

				<img src="images/login/bottom_right.png" width="540" height="11" alt=""><br />

				<!-- header menu -->
				<a href="<?php echo WIKI_URL; ?>/tutorials#video-tutorials" target="vid"><img src="images/login/video.png" width="166" height="29" alt="Video Tutorials"></a>
				<a href="<?php echo WIKI_URL; ?>" target="manu"><img src="images/login/manual2.png" width="129" height="29" alt="Wiki"></a>
				<a href="http://smrcnn.smrealms.de" target="board"><img src="images/login/webboard2.png" width="128" height="29" alt="Web Board"></a>
				<a href="album" target="ml"><img src="images/login/sml.png" width="166" height="29" alt="Merchant Library"></a>
				<a href="cgi-bin/awstats.pl" target="stat"><img src="images/login/stats2.png" width="180" height="29" alt="Site Statistics"></a>
				<br />
			</p>
			<?php

			if(isset($Message)) { ?>
				<h4 style="margin-bottom: 0px;"><?php echo $Message ?></h4><?php
			} ?>

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
										<input id="login_username" class="InputFields inputbox uncollapse" type="text" name="login"><br />
										<input id="login_password" class="InputFields inputbox uncollapse" type="password" name="password">
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
						<span style="font-size: 14px;">Or login with:</span>
						<br />
						<span>
							<a href="login_social_processing.php?type=facebook">
								<img style="padding-top:5px;" alt="Facebook Login" src="images/login/facebook.png" width="65" height="22">
							</a>
							<?php /*
							<a style="display: inline;margin:10px;" href="login_social_processing.php?type=google">
								<img alt="Google Login" src="images/login/google.png" width="32" height="32">
							</a>
							<a style="display: inline;margin:10px;" href="login_social_processing.php?type=twitter">
								<img alt="Twitter Login" src="images/login/twitter.png">
							</a>
							*/ ?>
						</span>
					</td>
					<td id="twitter-feed" class="hide" style="height:256px;width:90%;">
						<a class="twitter-timeline" data-dnt="true" data-height="250" data-theme="dark" data-chrome="transparent nofooter noheader noborders" href="https://twitter.com/SMRealms?ref_src=twsrc%5Etfw"></a>
						<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
					</td>
				</tr>
			</table><?php

			if(isset($GameNews)) { ?>
				<table class="standard center" style="width:730px;">
					<tr>
						<th>Time</th>
						<th>Recent News</th>
					</tr><?php
					foreach($GameNews as $News) { ?>
						<tr>
							<td><?php
								echo $News['Date']; ?><br /><?php
								echo $News['Time']; ?>
							</td>
							<td><?php echo $News['Message']; ?></td>
						</tr><?php
					} ?>
				</table><?php
			} ?>

			<br /><?php

			if(isset($Story)) { ?>
				<table class="center nobord" style="width:640px;">
					<tr>
						<td><?php
							foreach($Story as $StoryPart) { ?>
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
			<span class="small"><a href="imprint.html">[Imprint]</a></span>
		</div>

		<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
		<script src="js/login.js"></script>

	</body>
</html>
