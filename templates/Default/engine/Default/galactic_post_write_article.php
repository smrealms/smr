<?php
if(isset($Preview))
{ ?>
	<table class="standard">
		<tr>
			<td>Title:</td>
			<td><?php echo bbifyMessage($PreviewTitle); ?></td>
		</tr>
		<tr>
			<td>Article:</td>
			<td><?php echo bbifyMessage($Preview); ?></td>
		</tr>
	</table><br /><br /><?php
} ?>
What is the title?<br />
<form name="GPArticleForm" method="POST" action="<?php echo $SubmitArticleHref; ?>">
	<input type="text" name="title" id="InputFields" class="center" style="width:525;" value="<?php if(isset($PreviewTitle)) { echo htmlspecialchars($PreviewTitle); } ?>"><br /><br />
	<br />Write what you want to write here!<br />
	<textarea name="message" id="InputFields"><?php if(isset($Preview)) { echo $Preview; } ?></textarea><br /><br />
	<input type="submit" name="action" value="Submit article" id="InputFields" />&nbsp;<input type="submit" name="action" value="Preview article" id="InputFields" />
</form>