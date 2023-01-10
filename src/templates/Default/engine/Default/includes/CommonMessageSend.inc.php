<?php declare(strict_types=1);

if (isset($Preview)) { ?><table class="standard"><tr><td><?php echo bbifyMessage($Preview); ?></td></tr></table><?php } ?>
<form name="MessageSendForm" method="POST" action="<?php echo $MessageSendFormHref; ?>">
	<p>
		<b>From: </b><?php echo $ThisPlayer->getDisplayName(); ?><br />
		<b>To: </b><?php echo $Receiver; ?>
	</p>
	<textarea spellcheck="true" name="message" required><?php if (isset($Preview)) { echo $Preview; } ?></textarea><br />
	<br />
	<input type="submit" name="action" value="Send message" />&nbsp;<input type="submit" name="action" value="Preview message" />
</form>
