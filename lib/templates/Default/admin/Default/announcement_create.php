<?php if(isset($Preview)) { ?><table class="standard"><tr><td><?php echo bbifyMessage($Preview); ?></td></tr></table><br /><?php } ?>
<form name"AnnouncementCreateForm" method="POST" action="<?php echo $AnnouncementCreateFormHref; ?>">
	<textarea name="message" id="InputFields" cols="20" rows="30"><?php if(isset($Preview)) { echo $Preview; } ?></textarea><br />
	<input type="submit" name="action" value="Create announcement" id="InputFields" />&nbsp;<input type="submit" name="action" value="Preview announcement" id="InputFields" />
</form>