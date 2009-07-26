<?php $this->includeTemplate('includes/menu.inc',array('MenuItems' => array(
					array('Link'=>$ViewMessagesLink,'Text'=>'View Messages'),
					array('Link'=>$SendCouncilMessageLink,'Text'=>'Send Council Message'),
					array('Link'=>$SendGlobalMessageLink,'Text'=>'Send Global Message'),
					array('Link'=>$ManageBlacklistLink,'Text'=>'Manage Blacklist')))); ?>
<?php if(isset($Preview)) { ?><table class="standard"><tr><td><?php echo bbifyMessage($Preview); ?></td></tr></table><?php } ?>
<form name="MessageSendForm" method="POST" action="<?php echo $MessageSendFormHref; ?>">
<p>
	<small>
		<b>From: </b><?php echo $ThisPlayer->getDisplayName(); ?><br />
		<b>To: </b><?php if(isset($Reciever)) {	echo $Reciever->getDisplayName(); } else { ?>All Online<?php } ?>
	</small>
</p>
<textarea name="message" id="InputFields" style="width:350px;height:100px;"><?php if(isset($Preview)) { echo $Preview; } ?></textarea><br />
<br />
<input type="submit" name="action" value="Send message" id="InputFields" />&nbsp;
<input type="submit" name="action" value="Preview message" id="InputFields" />
</form>
</p>