Announcements are displayed to all users the next time they log in.<br />
You may use BBCode in your message, but not HTML.<br /><br />
<?php if (isset($Preview)) { ?><table class="standard"><tr><td><?php echo bbifyMessage($Preview); ?></td></tr></table><br /><?php } ?>
<form name="AnnouncementCreateForm" method="POST" action="<?php echo $AnnouncementCreateFormHref; ?>">
	<textarea required spellcheck="true" name="message"><?php if (isset($Preview)) { echo $Preview; } ?></textarea><br />
	<input type="submit" name="action" value="Create announcement" />&nbsp;<input type="submit" name="action" value="Preview announcement" />
</form>
