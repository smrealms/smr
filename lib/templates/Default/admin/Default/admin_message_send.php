<?php
if (!isset($MessageGameID))
{ ?>
	<form name="AdminMessageChooseGameForm" method="POST" action="<?php echo $AdminMessageChooseGameFormHref; ?>">
		<p>Please select a game:</p>
		<select name="game_id" size="1" id="InputFields">
			<option value="20000">Send to All Players</option><?php
			foreach($Games as &$Game)
			{
				?><option value="<?php echo $Game['ID']; ?>"><?php echo $Game['GameName']; ?></option><?php
			} unset($Game); ?>
		</select>&nbsp;&nbsp;
		<input type="submit" name="action" value="Select" id="InputFields" />
	</form><?php
}
else
{
	if(isset($Preview)) { ?><table class="standard"><tr><td><?php echo bbifyMessage($Preview); ?></td></tr></table><?php } ?>
	<form name="AdminMessageSendForm" method="POST" action="<?php echo $AdminMessageSendFormHref; ?>">
		<p>
		<b>From: </b><span class="admin">Administrator</span><br />
		<b>To: </b><?php
			if ($MessageGameID != 20000)
			{ ?>
				<select name="account_id" size="1" id="InputFields">
				<option value="0">[Please Select]</option><?php
				foreach($GamePlayers as $GamePlayer)
				{
					?><option value="<?php echo $GamePlayer['AccountID']; ?>"><?php echo $GamePlayer['Name']; ?> (<?php echo $GamePlayer['PlayerID']; ?>)</option><?php
				} ?>
				</select><br /><br /><?php
			} ?>
		</p>
		<textarea name="message" id="InputFields"><?php if(isset($Preview)) { echo $Preview; } ?></textarea><br />
		Hours Till Expire: <input type=text name=expire value=1 size=2 id=InputFields> (0 = never expire)<br />
		<br />
		<input type="submit" name="action" value="Send message" id="InputFields" />&nbsp;<input type="submit" name="action" value="Preview message" id="InputFields" />
	</form><?php
} ?>