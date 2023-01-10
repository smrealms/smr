<?php declare(strict_types=1);

?>
<div class="centered" style="width: 510px;">
	<h1><span class="red">ERROR</span></h1>

	<p class="big bold"><?php echo htmlentities($ErrorMessage, ENT_NOQUOTES, 'utf-8'); ?></p>
	<br />
	<p><small>If the error was caused by something you entered, press back and try again.</small></p>
	<p><small>If it was a game error, press back and try again, or logoff and log back on.</small></p>
	<p><small>If the error was unrecognizable, please notify the administrators.
		<a href="<?php echo WIKI_URL; ?>/contact-us">
			<img src="images/silk/help.png" width="16" height="16" alt="Wiki Link" title="Contact Us"/>
		</a>
	</small></p>

	<p><img src="images/escape_pod.jpg"></p>
</div>
