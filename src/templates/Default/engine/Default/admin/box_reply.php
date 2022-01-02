<a href="<?php echo $BackHREF; ?>">&lt;&lt; Back</a><br /><br />
<?php if (isset($Preview)) { ?><table class="standard"><tr><td><?php echo bbifyMessage($Preview); ?></td></tr></table><br /><?php } ?>
<form name="BoxReplyForm" method="POST" action="<?php echo $BoxReplyFormHref; ?>">
	<b>From: </b><span class="admin">Administrator</span><br />
	<b>To: </b><?php echo $Sender->getDisplayName(); ?> a.k.a <?php echo $SenderAccount->getLogin(); ?>
	<br />
	<textarea required spellcheck="true" name="message"><?php if (isset($Preview)) { echo $Preview; } ?></textarea><br /><br />
	<input type="number" value="<?php echo $BanPoints; ?>" name="BanPoints" size="4" /> Add Ban Points<br /><br />
	<input type="number" value="<?php echo $RewardCredits; ?>" name="RewardCredits" size="4" /> Add Reward Credits<br />
	<p>Sending the message will add ban points or reward credits, if specified above.</p>
	<input type="submit" name="action" value="Send message" />&nbsp;<input type="submit" name="action" value="Preview message" />
</form>
