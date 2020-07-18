<?php
if (isset($Preview)) { ?><table class="standard"><tr><td><?php echo bbifyMessage($Preview); ?></td></tr></table><?php } ?>
<form name="AdminMessageSendForm" method="POST" action="<?php echo $AdminMessageSendFormHref; ?>">
	<p>
	<b>From: </b><span class="admin">Administrator</span><br />
	<b>To: </b><?php
		if ($MessageGameID != 20000) { ?>
			<select name="account_id" size="1">
			<option value="0">[Please Select]</option><?php
			foreach ($GamePlayers as $GamePlayer) {
				?><option value="<?php echo $GamePlayer['AccountID']; ?>"><?php echo $GamePlayer['Name']; ?> (<?php echo $GamePlayer['PlayerID']; ?>)</option><?php
			} ?>
			</select><br /><br /><?php
		} else { ?>
			All Players<?php
		} ?>
	</p>
	<textarea required spellcheck="true" name="message"><?php if (isset($Preview)) { echo $Preview; } ?></textarea><br />
	Hours Till Expire: <input type="number" step="0.01" name="expire" value="<?php echo $ExpireTime; ?>" size="2"> (0 = never expire)<br />
	<br />
	<input type="submit" name="action" value="Send message" />&nbsp;<input type="submit" name="action" value="Preview message" />
</form>
