<?php declare(strict_types=1);

/**
 * @var Smr\Player $ThisPlayer
 * @var string $SendHREF
 * @var string $RaceName
 */

?>
<form method="POST" action="<?php echo $SendHREF; ?>">
	<p>
		<small>
			<b>From: </b><?php echo $ThisPlayer->getDisplayName(); ?><br />
			<b>To:</b> Ruling Council of <?php echo $RaceName; ?>
		</small>
	</p>

	<textarea spellcheck="true" name="message"></textarea>
	<br /><br />
	<input type="submit" name="action" value="Send message" />
</form>
