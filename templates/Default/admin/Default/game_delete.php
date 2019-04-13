<p>What game do you want to delete?</p>

<form method="POST" action="<?php echo $ConfirmHREF; ?>">
	<select name="delete_game_id" class="InputFields">
		<option value=None selected>[Select the game]</option><?php
		foreach ($Games as $Game) { ?>
			<option value="<?php echo $Game['game_id']; ?>"><?php echo $Game['display']; ?></option><?php
		} ?>
	</select>
	&nbsp;&nbsp;
	<input type="submit" name="action" value="Delete" />
</form>
