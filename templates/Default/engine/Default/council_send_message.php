<form method="POST" action="<?php echo $SendHREF; ?>">
	<p>
		<small>
			<b>From: </b><?php echo $ThisPlayer->getDisplayName(); ?><br />
			<b>To:</b> Ruling Council of <?php echo $RaceName; ?>
		</small>
	</p>

	<textarea spellcheck="true" name="message" class="InputFields"></textarea>
	<br /><br />
	<input type="submit" name="action" value="Send message" />
</form>
