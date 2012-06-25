<?php if(isset($Preview)) { ?><table class="standard"><tr><td><?php echo bbifyMessage($Preview); ?></td></tr></table><?php } ?>
<form name="MessageSendForm" method="POST" action="<?php echo $MessageSendFormHref; ?>">
	<p>
		<b>From: </b><?php echo $ThisPlayer->getDisplayName(); ?><br />
		<b>To: </b><?php if(isset($Reciever) && is_object($Reciever)) {	echo $Reciever->getDisplayName(); } else { echo $Reciever; } ?>
	</p>
	<textarea name="message" id="InputFields"><?php if(isset($Preview)) { echo $Preview; } ?></textarea><br />
	<br />
	<input type="submit" name="action" value="Send message" id="InputFields" />&nbsp;<input type="submit" name="action" value="Preview message" id="InputFields" />
</form>