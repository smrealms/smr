<?php if(isset($Preview)) { ?><table class="standard"><tr><td><?php echo bbifyMessage($Preview); ?></td></tr></table><?php } ?>
<form name="BoxReplyForm" method="POST" action="<?php echo $BoxReplyFormHref; ?>">
	<b>From: </b><span style="font:small-caps bold;color:blue;">Administrator</span><br />
	<b>To: </b><?php echo $Sender->getPlayerName(); ?> a.k.a <?php echo $SenderAccount->login; ?>
	<br />
	<input type="text" value="<?php if(isset($BanPoints)) { echo $BanPoints; } else { ?>0<?php } ?>" name="BanPoints" size="4" /> Points<br />
	<textarea name="message" id="InputFields"><?php if(isset($Preview)) { echo $Preview; } ?></textarea><br /><br />
	<input type="submit" name="action" value="Send message" id="InputFields" />&nbsp;<input type="submit" name="action" value="Preview message" id="InputFields" />
</form>