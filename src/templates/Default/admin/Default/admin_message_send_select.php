<form name="AdminMessageChooseGameForm" method="POST" action="<?php echo $AdminMessageChooseGameFormHref; ?>">
	<p>Please select a game:</p>
	<select name="SendGameID" size="1">
		<option value="20000">Send to All Players</option><?php
		foreach ($ActiveGames as $Game) {
			?><option value="<?php echo $Game->getGameID(); ?>"><?php echo $Game->getDisplayName(); ?></option><?php
		} ?>
	</select>&nbsp;&nbsp;
	<input type="submit" name="action" value="Select" />
</form>
