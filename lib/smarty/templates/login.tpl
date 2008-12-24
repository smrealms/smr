<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head><title>{$Title}</title>
{literal}
<script language="javascript">
<!--
writeCookie();

function writeCookie()
{
var today = new Date();
var the_date = new Date("December 31, 2023");
var the_cookie_date = the_date.toGMTString();
var the_cookie = "users_resolution="+ screen.width +"x"+ screen.height;
var the_cookie = the_cookie + ";expires=" + the_cookie_date;
document.cookie=the_cookie;
}
//-->
</script>
<style>
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
} 
#InputFields {
	background : #06240E;
	color : #FFFFFF;
	border : 1px solid #80C870;
	font-family: Arial;
	font-size:inherit;

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
a{
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
</style>
<!--[if IE]>
<style>
input.inputbox {
	width:121px;
	height:18px;
}
</style>
<![endif]-->
{/literal}
</head>

<body>
<div align="center">
<img src="images/login/smr_banner_res.gif"><br />
<img src="images/login/bottom_left.gif"><img src="images/login/home.gif"><img src="images/login/site_map.gif"><img src="images/login/contact.gif"><img src="images/login/bottom_right.gif"><br />
<a href="http://video.smrealms.de/" target="gw"><img src="images/login/video.gif"></a><a href="http://www.azool.us/baalz/" target="alb"><img src="images/login/sml.gif"></a><a href="http://smrcnn.smrealms.de" target="board"><img src="images/login/webboard2.gif"></a><a href="http://bobby.fem.tu-ilmenau.de/cgi-bin/awstats.pl?config=www.smrealms.de" target="stat"><img src="images/login/stats2.gif"></a><a href="http://www.smrealms.de/manual.php" target="manu"><img src="images/login/manual2.gif"></a><br />

{if !$isFirefox}
	<br />
	<a class="button" href="http://www.spreadfirefox.com/node&id=216853&t=210">
	<img alt="Get Firefox!" title="Get Firefox!" src="images/firefox.png"></a><br />
	SMR is primarly coded and tested with Firefox.<br />Firefox is recommended for the best gameplay. 
	<a href="http://www.spreadfirefox.com/node&id=$id&t=215">Download Firefox</a> now, it\'s free.
	<br />
{/if}

{if $isAprilFools}
	<a href="http://www.smrealms.de/merge.php"><span class="red bold">Important Announcement</span></a> : SMR and TDZK are set to merge. 
	Please <a href="http://www.smrealms.de/merge.php">click here</a> for more details.
{/if}

{if $Message}
	<h4 style="margin-bottom: 0px;">{$Message}</h4>
{/if}

<table border="0" cellspacing="20" cellpadding="10" width="770px">
	<tr>
		<td width="300px">
			<form action="login_processing.php" method="post">
				<table border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td colspan="3">
							<img src="images/login/login_top.gif">
						</td>
					</tr>
					<tr>
						<td>
							<img src="images/login/loginPw.gif" />
						</td>
						<td>
							<input id="InputFields" class="inputbox" type="text" name="login"><br />
							<input id="InputFields" type="password" class="inputbox" name="password">
						</td>
						<td>
							<img src="images/login/loginPwRight.gif">
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<img src="images/login/loginPwMid.gif">
						</td>
					</tr>
				</table>
				<table border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td>
							<img src="images/login/regLeft.gif"><a href="{$Links.Register}"><img src="images/login/register.gif"></a><a href="resend_password.php"><img src="images/login/pw_reset.gif"></a><img src="images/login/regRight.gif">
						</td>
					</tr>
					<tr>
						<td>
							<img src="images/login/loginMid.gif">
						</td>
					</tr>
					<tr>
						<td>
							<img src="images/login/enter_left.gif"><input type="image" SRC="images/login/enter.gif"><img src="images/login/enter_right.gif">
						</td>
					</tr>
					<tr>
						<td>
							<img src="images/login/enter_bottom.gif">
						</td>
					</tr>
				</table>
			</form>
		</td>
		<td width="470px">
			{if $LoginNews}
				<table border="0" class="standard" cellspacing="0" cellpadding="5">
				<tr><th>Date</th><th>News</th></tr>
				{foreach from=$LoginNews item=News}
					<tr><td><span class="small">
					{if $News.Recent} * {/if}
					{$News.Time}</span></td>
					<td><span class="small">{$News.Message}
					{if $AdminName}<br /><br />-{$AdminName}{/if}
					</span></td></tr>
				{/foreach}
				</table>
			{/if}
			</td></tr>
			
			<tr><td align="center" colspan="2">
			Players Online Now: {$NumberOnCPL}<br /><br />
			Recent News<br />
			<table class="standard" width="100%"><tr><th align="center">Time</th><th align="center">News</th></tr>
			{foreach from=$GameNews item=News}
				<tr><td>{$News.Date}<br />{$News.Time}</td><td>{$News.Message}</td></tr>
			{/foreach}
			</table>
		</td>
	</tr>
</table>

<br />

{if $Story}
	<table border="0" width="640px;"><tr><td colspan="2">
	{foreach from=$Story item=StoryPart}
		<p align="justify"><span class="small"><font face="Verdana, Arial, Helvetica" color="#FFFFFF">{$StoryPart}</font></span></p>
	{/foreach}
	</td></tr></table>
{/if}
<br /><br />
<span class="small"><a href="imprint.html">[Imprint]</a></span>
</div>