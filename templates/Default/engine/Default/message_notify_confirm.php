You have selected the following message:<br /><br />
<table class="standard">
	<tr>
		<td><?php echo bbifyMessage($MessageText); ?></td>
	</tr>
</table>

<p>Are you sure you want to report this message to the admins?<br />
<small><b>Please note:</b> Abuse of this system could end in disablement.<br />Therefore, please only notify if the message is inappropriate.</small></p>

<form method="POST" action="<?php echo $ProcessingHREF; ?>">
	<input type="submit" name="action" value="Yes" />
	&nbsp;&nbsp;
	<input type="submit" name="action" value="No" />
</form>
