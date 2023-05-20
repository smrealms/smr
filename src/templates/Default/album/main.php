<?php declare(strict_types=1);

/**
 * @var array<string, int> $MostViewed
 * @var array<string, int> $Newest
 */

?>
<p>
	<u>Space Merchant Realms Photo Album Rules</u>
	<ol>
		<li>500 x 500 pixel maximum photo size.</li>
		<li>Only .jpg, .png, or .gif files will be accepted.</li>
		<li>No derogatory or vulgar pictures will be accepted.</li>
		<li>Pictures MUST depict the real you. No anime, fictional, or otherwise "fake" pictures are allowed.</li>
		<li>Please watch your language while posting within the album. Same general rules apply here as in SMR chat rooms.</li>
		<li>Please respect all members in this area. Treat them as you would want to be treated. Do not post cruel or otherwise unneeded comments about someone or their property.</li>
		<li>You must be logged into your account to post within this album. Therefore, if you break any of these rules, your account may be subject to disablement.</li>
	</ol>
	<small><b>Please Note:</b> This is your only warning! All rule violations (even first time offenders) will be subject to a 1-day ban. Repeat offenders may incur longer bans.</small>
</p>

<p>&nbsp;</p>

<p>
	<u>Top 5 Pictures</u>
	<br /><br /><?php
	foreach ($MostViewed as $Nick => $PageViews) { ?>
		<a href="?nick=<?php echo urlencode($Nick); ?>"><?php echo htmlentities($Nick); ?></a> (<?php echo $PageViews; ?>)<br /><?php
	} ?>
</p>

<p>
	<u>Latest Pictures</u>
	<br /><br /><?php
	foreach ($Newest as $Nick => $Created) { ?>
		<span style="font-size:85%;">
			<b>[<?php echo $Created; ?>]</b> Picture of <a href="?nick=<?php echo urlencode($Nick); ?>"><?php echo htmlentities($Nick); ?></a> added
		</span><br /><?php
	} ?>
</p>
