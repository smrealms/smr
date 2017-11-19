<?php echo $Form['form']; ?>
<table cellspacing="0" cellpadding="0" class="nobord nohpad">

<?php
if ($CanChangePassword) { ?>
	<tr>
		<td class="top">Password:&nbsp;</td><td><input type="password" name="password" size="30" value="<?php echo htmlspecialchars($Alliance->getPassword()); ?>"></td>
	</tr><?php
}

if ($CanChangeDescription) { ?>
	<tr>
		<td class="top">Description:&nbsp;</td><td><textarea spellcheck="true" name="description"><?php echo $Alliance->getDescription(); ?></textarea></td>
	</tr><?php
}

if ($CanChangeChatChannel) { ?>
	<tr>
		<td class="top">IRC Channel:&nbsp;</td>
		<td><input type="text" name="irc" size="30" value="<?php echo htmlspecialchars($Alliance->getIrcChannel()); ?>">(For Caretaker and autojoining via chat link - works best if you join the channel using the chat link and type "/autoconnect on" as an op)</td>
	</tr><?php
}

if ($CanChangeMOTD) { ?>
	<tr>
		<td class="top">Image URL:&nbsp;</td><td><input type="url" name="url" size="30" value="<?php echo htmlspecialchars($Alliance->getImageURL()); ?>"></td>
	</tr>

	<tr>
		<td class="top">Message Of The Day:&nbsp;</td><td><textarea spellcheck="true" name="mod"><?php echo $Alliance->getMotD(); ?></textarea></td>
	</tr><?php
} ?>

</table>
<br />
<?php echo $Form['submit']; ?>
</form>
