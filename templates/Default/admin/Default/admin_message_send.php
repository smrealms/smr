<?php
if (!isset($MessageGameID)) { ?>
	<form name="AdminMessageChooseGameForm" method="POST" action="<?php echo $AdminMessageChooseGameFormHref; ?>">
		<p>Please select a game:</p>
		<select name="SendGameID" size="1" id="InputFields">
			<option value="20000">Send to All Players</option><?php
			foreach ($ActiveGames as $Game) {
				?><option value="<?php echo $Game->getGameID(); ?>"><?php echo $Game->getDisplayName(); ?></option><?php
			} ?>
		</select>&nbsp;&nbsp;
		<input type="submit" name="action" value="Select" id="InputFields" />
	</form><?php
}
else {
	if(isset($Preview)) { ?><table class="standard"><tr><td><?php echo bbifyMessage($Preview); ?></td></tr></table><?php } ?>
	<form name="AdminMessageSendForm" method="POST" action="<?php echo $AdminMessageSendFormHref; ?>">
		<p>
		<b>From: </b><span class="admin">Administrator</span><br />
		<b>To: </b><?php
			if ($MessageGameID != 20000) { ?>
				<select name="account_id" size="1" id="InputFields">
				<option value="0">[Please Select]</option><?php
				foreach($GamePlayers as $GamePlayer) {
					?><option value="<?php echo $GamePlayer['AccountID']; ?>"><?php echo $GamePlayer['Name']; ?> (<?php echo $GamePlayer['PlayerID']; ?>)</option><?php
				} ?>
				</select><br /><br /><?php
			} else { ?>
				All Players<?php
			} ?>
		</p>
		<textarea spellcheck="true" name="message" id="InputFields"><?php if(isset($Preview)) { echo $Preview; } ?></textarea><br />
		Hours Till Expire: <input type="number" step="0.01" name="expire" value="<?php echo $ExpireTime; ?>" size="2" id="InputFields"> (0 = never expire)<br />
		<br />
		<input type="submit" name="action" value="Send message" id="InputFields" />&nbsp;<input type="submit" name="action" value="Preview message" id="InputFields" />
	</form><?php
} ?>
