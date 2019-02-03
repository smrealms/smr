<form method="POST" action="<?php echo $SubmitHREF; ?>">
	Select the player you want to add the bounty to<br />
	<select name="player_id" size="1" id="InputFields">
		<option value="0">[Please Select]</option>

		<?php
		foreach ($BountyPlayers as $id => $name) { ?>
			<option value="<?php echo $id; ?>"><?php echo $name; ?></option><?php
		} ?>
	</select>

	<br /><br />
	Enter the amount you wish to place on this player<br />
	<table class="standardnobord">
		<tr>
			<td>Credits:</td>
			<td><input type="number" name="amount" maxlength="10" size="10" id="InputFields"></td>
		</tr>
		<tr>
			<td>Smr Credits:</td>
			<td><input type="number" name="smrcredits" maxlength="10" size="10" id="InputFields"></td>
		</tr>
	</table>

	<br /><br />
	<input type="submit" name="action" value="Place" />
</form>
