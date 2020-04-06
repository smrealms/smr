<?php echo $Form['form']; ?>
<table cellspacing="0" cellpadding="0" class="nobord nohpad">

<?php
if ($CanChangePassword) { ?>
	<tr>
		<td class="top">Recruiting:</td>
		<td>
			<select name="recruit_type" class="InputFields" onchange="togglePassword(this)"><?php
				foreach (SmrAlliance::allRecruitTypes() as $type => $text) { ?>
					<option value="<?php echo $type; ?>" <?php if ($Alliance->getRecruitType() == $type) { ?> selected<?php } ?>><?php echo $text; ?></option><?php
				} ?>
			</select>
		</td>
	</tr>
	<tr id="password-display" <?php if ($HidePassword) { ?> class="hide" <?php } ?>>
		<td class="top">Password:&nbsp;</td>
		<td><input required id="password-input" name="password" size="30" placeholder=" Enter password here" value="<?php echo htmlspecialchars($Alliance->getPassword()); ?>" <?php if ($HidePassword) { echo "disabled"; } ?>></td>
	</tr><?php
}

if ($CanChangeDescription) { ?>
	<tr>
		<td class="top">Description:&nbsp;</td><td><textarea spellcheck="true" name="description"><?php echo $Alliance->getDescription(); ?></textarea></td>
	</tr><?php
}

if ($CanChangeChatChannel) { ?>
	<tr>
		<td class="top">Discord Server ID:&nbsp;</td>
		<td>
			<input type="text" name="discord_server" size="30" value="<?php echo htmlspecialchars($Alliance->getDiscordServer()); ?>" />&nbsp;
			<a href="<?php echo WIKI_URL; ?>/chat#alliance-discord-widget" target="_blank">
				<img src="images/silk/help.png" width="16" height="16" alt="" title="Goto SMR Wiki: Discord Widget"/>
			</a>
			<br />Adds the Discord join server widget to the Message of the Day page.
		</td>
	<tr>
		<td class="top noWrap">Discord Channel ID:&nbsp;</td>
		<td>
			<input type="text" name="discord_channel" size="30" value="<?php echo htmlspecialchars($Alliance->getDiscordChannel()); ?>" />&nbsp;
			<a href="<?php echo WIKI_URL; ?>/chat#autopilot-for-alliance-channels" target="_blank">
				<img src="images/silk/help.png" width="16" height="16" alt="" title="Goto SMR Wiki: Autopilot"/>
			</a>
			<br />Enables Autopilot in this Discord channel.
		</td>
	</tr>
	<tr>
		<td class="top">IRC Channel:&nbsp;</td>
		<td>
			<input type="text" name="irc" size="30" value="<?php echo htmlspecialchars($Alliance->getIrcChannel()); ?>">&nbsp;
			<a href="<?php echo WIKI_URL; ?>/chat#caretaker-for-alliance-channels" target="_blank">
				<img src="images/silk/help.png" width="16" height="16" alt="" title="Goto SMR Wiki: Caretaker"/>
			</a>
		<br />Enables Caretaker in this IRC channel and autojoining via chat link.
		</td>
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
