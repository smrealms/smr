<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $Title; ?></title>
		<style type="text/css">
		body{
			background:#082010;
			font-family: Arial, sans-serif;
			font-variant:normal;
			margin:0px;
			padding:0px;
			margin-top:10px;
			color:#ffffff;
		}
		a.nobord {
			text-decoration:none;
		}
		img {
			border: 0px none;
			padding: 0px;
		}
		.InputFields {
			background : #06240E;
			color : #FFFFFF;
			border : 1px solid #80C870;
			font-family: Arial;
		}
		table.standard {
			border:1px solid #0b8d35;
		}
		.standard td,th{
			border:1px solid #0b8d35;
		}
		input.inputbox {
			width:123px;
			height:20px;
		}
		a {
			color:#ffffff;
			text-decoration:none;
			padding:0px;
			margin:0px;
		}

		a:hover {
			background:#0A4E1D;
		}

		a.header {
			color:#80C870;
		}

		.yellow {
			color:#ffff00;
		}
		.red {
			color:#ff0000;
		}
		span.small {
			font-size:75%;
		}
		.bold {
			font-weight:bold;
		}
		.dgreen {
			color: #008000;
		}
		.center {
			text-align: center;
		}
		table.center {
			margin:auto;
		}
		.collapsed, table.collapsed, table.collapsed tr, table.collapsed th, table.collapsed td {
			border: 0px;
			border-spacing: 0px;
			border-collapse: collapse;
			padding: 0px;
			margin: 0px;
			font-size: 0px;
		}
		input.uncollapse {
			font-size: 15px;
		}
		table.nobord {
			border: 0px;
		}
		</style>
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
				<img src="images/login/smr_banner_16.png" alt=""><br />
				<img src="images/login/bottom_left.gif" alt=""><a href="<?php echo URL ?>"><img src="images/login/home.png" alt="Home"></a><img src="images/login/site_map.png" alt="Site Map"><img src="images/login/contact.png" alt="Contact"><img src="images/login/bottom_right.png" alt=""><br />
				<a href="http://video.smrealms.de/" target="vid"><img src="images/login/video.png" alt="Video Tutorials"></a><a href="http://manual.smrealms.de" target="ml"><img src="images/login/sml.png" alt="Merchant Library"></a><a href="http://www.smrealms.de/manual.php" target="manu"><img src="images/login/manual2.png" alt="Manual"></a><a href="http://smrcnn.smrealms.de" target="board"><img src="images/login/webboard2.png" alt="Web Board"></a><a href="http://www.smrealms.de/awstats/awstats.pl?config=www.smrealms.de" target="stat"><img src="images/login/stats2.png" alt="Site Statistics"></a><br />
			</p>
			<?php
			/*
			if(!$isFirefox) { ?>
				<br />
				<a class="button" href="http://www.spreadfirefox.com/node&amp;id=216853&amp;t=210">
				<img alt="Get Firefox!" title="Get Firefox!" src="images/firefox.png"></a><br />
				SMR is primarly coded and tested with Firefox.<br />Firefox is recommended for the best gameplay.
				<a href="http://www.spreadfirefox.com/node&amp;id=216853&amp;t=215">Download Firefox</a> now, it's free.
				<br /><?php
			}

			/*if($isAprilFools) { ?>
				<a href="http://www.smrealms.de/merge.php"><span class="red bold">Important Announcement</span></a> : SMR and TDZK are set to merge.
				Please <a href="http://www.smrealms.de/merge.php">click here</a> for more details.<?php
			} */

			if(isset($Message)) { ?>
				<h4 style="margin-bottom: 0px;"><?php echo $Message ?></h4><?php
			} ?>

			<table class="center nobord" style="width:770px; border-spacing:20px; padding:10px">
				<tr>
					<td style="width:300px">
						<form action="login_processing.php" method="post">
							<table class="collapsed">
								<tr>
									<td colspan="3">
										<img src="images/login/login_top.png" alt="">
									</td>
								</tr>
								<tr>
									<td>
										<img src="images/login/loginPw.png" alt="Login/Password" />
									</td>
									<td>
										<input id="login_username" class="InputFields inputbox uncollapse" type="text" name="login"><br />
										<input id="login_password" class="InputFields inputbox uncollapse" type="password" name="password">
									</td>
									<td>
										<img src="images/login/loginPwRight.png" alt="">
									</td>
								</tr>
								<tr>
									<td colspan="3">
										<img src="images/login/loginPwMid.png" alt="">
									</td>
								</tr>
							</table>
							<table class="collapsed">
								<tr>
									<td>
										<img src="images/login/regLeft.png" alt=""><a href="<?php echo $Links['Register'] ?>"><img src="images/login/register.png" alt="Register"></a><a href="resend_password.php"><img src="images/login/pw_reset.png" alt="Reset Password"></a><img src="images/login/regRight.png" alt="">
									</td>
								</tr>
								<tr>
									<td>
										<img src="images/login/loginMid.png" alt="">
									</td>
								</tr>
								<tr>
									<td>
										<img src="images/login/enter_left.png" alt=""><input id="login_submit" type="image" src="images/login/enter.png" alt="Enter"><img src="images/login/enter_right.png" alt="">
									</td>
								</tr>
								<tr>
									<td>
										<img src="images/login/enter_bottom.png" alt="">
									</td>
								</tr>
							</table>
						</form>
					</td>
					<td style="text-align: center; padding: 3px; width: 200px;">
						<span style="font-size: 14px;">Or login with:</span>
						<span style="font-size: 3px;"><br /><br />
							<?php require_once(LIB.'Login/SocialLogin.class.inc'); ?>
							<a style="display: inline;margin:10px;" href="<?php echo str_replace('&', '&amp;', SocialLogin::getFacebookLoginUrl()); ?>">
								<img alt="Facebook Login" src="images/login/facebook.png">
							</a><br /><br />
							<a style="display: inline;margin:10px;" href="<?php echo str_replace('&', '&amp;', SocialLogin::getOpenIdLoginUrl('Google')); ?>">
								<img alt="Google Login" src="images/login/google.png">
							</a>&nbsp;
							<a style="display: inline;margin:10px;" href="<?php echo str_replace('&', '&amp;', SocialLogin::getTwitterLoginUrl()); ?>">
								<img alt="Twiiter Login" src="images/login/twitter.png">
							</a>
						</span>
					</td>
					<td style="width:470px"><?php
						if(isset($LoginNews)) { ?>
							<table class="standard">
							<tr><th>Date</th><th>News</th></tr><?php
							foreach($LoginNews as $News) { ?>
								<tr>
									<td>
										<span class="small"><?php
											if($News['Recent']){ ?> * <?php }
											echo $News['Time'] ?>
										</span>
									</td>
									<td>
										<span class="small"><?php echo bbifyMessage($News['Message']);
											if($News['AdminName']) { ?>
												<br /><br />- <?php echo $News['AdminName'];
											} ?>
										</span>
									</td>
								</tr><?php
							} ?>
							</table><?php
						} ?>
					</td>
				</tr>

				<tr>
					<td class="center" colspan="3">
						Players Online Now: <?php echo $ActiveSessions; ?><br /><br /><?php
						if(isset($GameNews)) { ?>
							Recent News<br />
							<table class="standard" style="width:100%"><tr><th class="center">Time</th><th class="center">News</th></tr><?php
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
					</td>
				</tr>
			</table>

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
	</body>
</html>