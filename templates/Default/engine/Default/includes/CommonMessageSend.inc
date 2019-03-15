<?php if(isset($Preview)) { ?><table class="standard"><tr><td><?php echo bbifyMessage($Preview); ?></td></tr></table><?php } ?>
<form name="MessageSendForm" method="POST" action="<?php echo $MessageSendFormHref; ?>">
	<p>
		<b>From: </b><?php echo $ThisPlayer->getDisplayName(); ?><br />
		<b>To: </b><?php if(isset($Receiver) && is_object($Receiver)) {	echo $Receiver->getDisplayName(); } else { echo $Receiver; } ?>
	</p>
	<textarea spellcheck="true" name="message" class="InputFields"><?php if(isset($Preview)) { echo $Preview; } ?></textarea><br />
	<br />
	<input type="submit" name="action" value="Send message" class="InputFields" />&nbsp;<input type="submit" name="action" value="Preview message" class="InputFields" />
</form>
